<?php
namespace Jackminh\Miaosha\Services;

use Jackminh\Miaosha\Repositories\SeckillActivityRepository;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AntifraudService
{

    protected array $config;

    public function __construct(array $config = []){
        $this->config = $config ?: config("miaosha",[]);
        
    }


    // 1. IP限制
    public function checkIpLimit($ip, $activityId)
    {
        $ipKey = "seckill:ip_limit:{$activityId}:{$ip}";
        $count = Redis::connection('seckill')->incr($ipKey);
        if ($count == 1) {
            Redis::connection('seckill')->expire($ipKey, 60); // 60秒内限制
        }
        return $count <= 10; // 60秒内最多10次请求
    }
    
    // 2. 设备指纹
    public function checkDeviceFingerprint($fingerprint, $activityId)
    {
        $deviceKey = "seckill:device:{$activityId}:{$fingerprint}";
        if (Redis::connection('seckill')->exists($deviceKey)) {
            return false; // 设备已参与
        }
        Redis::connection('seckill')->setex($deviceKey, 10, 1); // 10秒内不能重复参与
        return true;
    }

    // 3. 频率限制（同一用户、同一IP）
    public function rateLimit($activityId,$userId){
        $rateLimitKey = "seckill:ratelimit:{$activityId}:{$userId}";
        if(Redis::exists($rateLimitKey)) {
            return false;
        }
        Redis::setex($rateLimitKey, 1, 1); // 1秒内只能请求一次
        return true;
    }

    /**
     * 分析用户行为
    */
    public function analyzeUserBehavior($userId, $activityId): bool
    {
        $behaviorKey = "seckill:behavior:{$userId}:{$activityId}";
        $connection = Redis::connection('seckill');
        
        // 获取用户行为记录
        $behaviors = $connection->lrange($behaviorKey, 0, -1);
        
        // 基础频率检查
        if (!$this->checkFrequency($behaviors)) {
            $this->addToBlacklist($userId, $activityId, "高频请求");
            return false;
        }
        
        // 当有足够数据时进行模式分析
        if (count($behaviors) >= $this->config['behavior']['analyze_threshold']) {
            
            // 计算请求间隔
            $intervals = $this->calculateRequestInterval($behaviors);
            
            // 1. 检查是否为机器人模式
            if ($this->isRoboticPattern($intervals)) {
                $this->addToBlacklist($userId, $activityId, "机器人模式");
                return false;
            }
            
            // 2. 检查请求间隔的均匀性
            if ($this->isUniformInterval($intervals)) {
                $this->addToBlacklist($userId, $activityId, "均匀间隔请求");
                return false;
            }
            
            // 3. 检查是否有固定模式
            if ($this->hasFixedPattern($intervals)) {
                $this->addToBlacklist($userId, $activityId, "固定请求模式");
                return false;
            }
            
            // 4. 检查时间窗口内的爆发请求
            if ($this->hasBurstPattern($behaviors)) {
                $this->addToBlacklist($userId, $activityId, "爆发式请求");
                return false;
            }
        }
        
        // 记录本次请求
        $connection->lpush($behaviorKey, microtime(true));
        $connection->ltrim($behaviorKey, 0, $this->config['behavior']['max_records'] - 1);
        
        // 设置过期时间，避免数据无限增长
        $connection->expire($behaviorKey, 86400); // 24小时
        
        return true;
    }


    /**
     * 计算请求间隔（返回毫秒数组）
     */
    protected function calculateRequestInterval(array $behaviors): array
    {
        $intervals = [];
        // 将时间戳转换为浮点数并排序
        $timestamps = array_map('floatval', $behaviors);
        sort($timestamps);
        
        // 计算相邻请求的时间间隔（毫秒）
        for ($i = 1; $i < count($timestamps); $i++) {
            $interval = ($timestamps[$i] - $timestamps[$i - 1]) * 1000; // 转换为毫秒
            $intervals[] = round($interval, 2);
        }
        
        return $intervals;
    }



     /**
     * 检查基础频率
     */
    protected function checkFrequency(array $behaviors): bool
    {
        if (count($behaviors) < 10) {
            return true;
        }
        $timestamps = array_map('floatval', $behaviors);
        $earliest   = min($timestamps);
        $latest     = max($timestamps);
        
        // 计算每分钟的请求频率
        $timeSpan = $latest - $earliest;
        if ($timeSpan <= 0) {
            return false;
        }
        
        $frequency = count($timestamps) / ($timeSpan / 60);
        
        return $frequency <= $this->config['behavior']['max_frequency'];
    }


    /**
     * 判断是否为机器人模式
     */
    protected function isRoboticPattern(array $intervals): bool
    {
        if (count($intervals) < 10) {
            return false;
        }
        
        // 1. 计算间隔的变异系数（Coefficient of Variation）
        $cv = $this->calculateCoefficientOfVariation($intervals);
        if ($cv < $this->config['robot_detection']['variance_threshold']) {
            return true; // 变异系数太小，说明间隔非常均匀
        }
        
        // 2. 检查标准差
        $stdDev = $this->calculateStandardDeviation($intervals);
        if ($stdDev < $this->config['robot_detection']['std_dev_threshold']) {
            return true; // 标准差太小，说明间隔变化很小
        }
        
        // 3. 检查间隔的熵值（衡量随机性）
        $entropy = $this->calculateIntervalEntropy($intervals);
        if ($entropy < 2.0) { // 经验值，正常人类操作应该有更高的熵值
            return true;
        }
        
        return false;
    }

    /**
     * 判断间隔是否过于均匀
     */
    protected function isUniformInterval(array $intervals): bool
    {
        if (count($intervals) < 5) {
            return false;
        }
        
        // 计算间隔的相对标准差（RSD）
        $mean = array_sum($intervals) / count($intervals);
        if ($mean == 0) {
            return false;
        }
        
        $variance = 0;
        foreach ($intervals as $interval) {
            $variance += pow($interval - $mean, 2);
        }
        $variance /= count($intervals);
        $stdDev = sqrt($variance);
        $rsd = ($stdDev / $mean) * 100;
        
        // RSD小于5%说明非常均匀
        return $rsd < 5;
    }


    /**
     * 检查是否有固定模式（如：100ms, 200ms, 100ms, 200ms...）
     */
    protected function hasFixedPattern(array $intervals): bool
    {
        if (count($intervals) < 10) {
            return false;
        }
        
        // 寻找重复的模式序列
        for ($patternLength = 2; $patternLength <= 5; $patternLength++) {
            if ($this->detectRepeatingPattern($intervals, $patternLength)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 检查爆发式请求
     */
    protected function hasBurstPattern(array $behaviors): bool
    {
        $timestamps = array_map('floatval', $behaviors);
        rsort($timestamps); // 从最新到最旧排序
        
        // 检查最近1秒内的请求数
        $recentRequests = 0;
        $currentTime = microtime(true);
        
        foreach ($timestamps as $timestamp) {
            if (($currentTime - $timestamp) <= 1.0) {
                $recentRequests++;
            } else {
                break;
            }
        }
        
        // 正常人类很难在1秒内点击超过20次
        return $recentRequests > 20;
    }
    
    /**
     * 计算变异系数
     */
    protected function calculateCoefficientOfVariation(array $data): float
    {
        $mean = array_sum($data) / count($data);
        if ($mean == 0) {
            return 0;
        }
        
        $variance = 0;
        foreach ($data as $value) {
            $variance += pow($value - $mean, 2);
        }
        $variance /= count($data);
        $stdDev = sqrt($variance);
        
        return $stdDev / $mean;
    }
    
    /**
     * 计算标准差
     */
    protected function calculateStandardDeviation(array $data): float
    {
        $mean = array_sum($data) / count($data);
        $variance = 0;
        
        foreach ($data as $value) {
            $variance += pow($value - $mean, 2);
        }
        $variance /= count($data);
        
        return sqrt($variance);
    }
    
    /**
     * 计算间隔的熵值
     */
    protected function calculateIntervalEntropy(array $intervals): float
    {
        // 将间隔分组
        $bins = [0, 50, 100, 200, 500, 1000, 2000, 5000, 10000]; // 毫秒
        $counts = array_fill(0, count($bins), 0);
        
        foreach ($intervals as $interval) {
            for ($i = 0; $i < count($bins); $i++) {
                if ($interval <= $bins[$i]) {
                    $counts[$i]++;
                    break;
                }
            }
        }
        
        // 计算熵值
        $entropy = 0;
        $total = array_sum($counts);
        
        foreach ($counts as $count) {
            if ($count > 0) {
                $probability = $count / $total;
                $entropy -= $probability * log($probability, 2);
            }
        }
        
        return $entropy;
    }
    
    /**
     * 检测重复模式
     */
    protected function detectRepeatingPattern(array $data, int $patternLength): bool
    {
        if (count($data) < $patternLength * 3) {
            return false;
        }
        
        $repeats = 0;
        $maxRepeats = floor(count($data) / $patternLength);
        
        for ($i = 0; $i < $maxRepeats - 1; $i++) {
            $currentPattern = array_slice($data, $i * $patternLength, $patternLength);
            $nextPattern = array_slice($data, ($i + 1) * $patternLength, $patternLength);
            
            // 计算两个模式的相似度
            if ($this->patternsAreSimilar($currentPattern, $nextPattern)) {
                $repeats++;
                if ($repeats >= $this->config['robot_detection']['pattern_repeat_threshold']) {
                    return true;
                }
            } else {
                $repeats = 0;
            }
        }
        
        return false;
    }
    
    /**
     * 判断两个模式是否相似
     */
    protected function patternsAreSimilar(array $pattern1, array $pattern2, float $tolerance = 0.1): bool
    {
        if (count($pattern1) != count($pattern2)) {
            return false;
        }
        
        for ($i = 0; $i < count($pattern1); $i++) {
            if ($pattern1[$i] == 0 || $pattern2[$i] == 0) {
                continue;
            }
            
            $ratio = $pattern1[$i] / $pattern2[$i];
            if ($ratio < (1 - $tolerance) || $ratio > (1 + $tolerance)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 添加到黑名单
     */
    public function addToBlacklist($userId, $activityId, string $reason = ''): void
    {
        $blacklistKey = $this->config['blacklist']['prefix'] . "{$activityId}:{$userId}";
        $connection = Redis::connection('seckill');
        
        $data = [
            'user_id' => $userId,
            'activity_id' => $activityId,
            'reason' => $reason,
            'banned_at' => now()->toDateTimeString(),
            'expires_at' => now()->addSeconds($this->config['blacklist']['ttl'])->toDateTimeString(),
        ];
        
        $connection->setex(
            $blacklistKey,
            $this->config['blacklist']['ttl'],
            json_encode($data)
        );
        
        // 记录日志
        \Log::warning('用户被加入秒杀黑名单', $data);
    }
    
    /**
     * 检查是否在黑名单中
     */
    public function isInBlacklist($userId, $activityId): bool
    {
        $blacklistKey = $this->config['blacklist']['prefix'] . "{$activityId}:{$userId}";
        return Redis::connection('seckill')->exists($blacklistKey);
    }
    
    /**
     * 获取用户行为统计
     */
    public function getUserBehaviorStats($userId, $activityId): array
    {
        $behaviorKey = "seckill:behavior:{$userId}:{$activityId}";
        $behaviors = Redis::connection('seckill')->lrange($behaviorKey, 0, -1);
        
        if (empty($behaviors)) {
            return [];
        }
        
        $timestamps = array_map('floatval', $behaviors);
        sort($timestamps);
        
        $intervals = $this->calculateRequestInterval($behaviors);
        
        return [
            'total_requests' => count($behaviors),
            'time_span' => end($timestamps) - reset($timestamps),
            'avg_interval' => count($intervals) > 0 ? array_sum($intervals) / count($intervals) : 0,
            'std_dev' => $this->calculateStandardDeviation($intervals),
            'cv' => $this->calculateCoefficientOfVariation($intervals),
            'entropy' => $this->calculateIntervalEntropy($intervals),
            'is_suspicious' => $this->isRoboticPattern($intervals),
        ];
    }
    
    /**
     * 清理过期行为记录
     */
    public function cleanupOldRecords(int $hours = 24): void
    {
        $connection = Redis::connection('seckill');
        $pattern = "seckill:behavior:*";
        
        // 注意：在生产环境中，应该使用SCAN而不是KEYS
        $keys = $connection->keys($pattern);
        
        foreach ($keys as $key) {
            $ttl = $connection->ttl($key);
            if ($ttl < 0) { // 没有设置过期时间或已过期
                $connection->expire($key, $hours * 3600);
            }
        }
    }



}


