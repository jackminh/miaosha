<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IhUsersSeeder extends Seeder
{
    private array $domains = [
        'gmail.com', 'yahoo.com', 'hotmail.com', 
        'outlook.com', 'qq.com', '163.com', '126.com'
    ];
    
    private array $nicknamePrefixes = [
        '快乐', '阳光', '勇敢', '智慧', '自由',
        '飞翔', '梦想', '星空', '海洋', '山河'
    ];
    
    private array $nicknameSuffixes = [
        '的小屋', '的旅程', '的世界', '的笔记', '的空间',
        '达人', '高手', '玩家', '爱好者', '探索者'
    ];
    
    public function run(): void
    {
        $batchSize  = 10;    // 每批插入数量
        $totalUsers = 1000; // 总用户数
        $batches = ceil($totalUsers / $batchSize);
        for($batch = 0; $batch < $batches; $batch++) {
            $users = [];
            for($i = 0; $i < $batchSize; $i++) {
                if(($batch * $batchSize + $i) >= $totalUsers) {
                    break;
                }
                $firstName = $this->generateChineseFirstName();
                $lastName = $this->generateChineseLastName();
                $name = $lastName . $firstName;
                $users[] = [
                    'sn'         => create_user_sn(),
                    'real_name'  => $name,
                    'nickname'   => $this->generateUniqueNickname(),
                    'account'    => $this->generateUniqueEmail(),
                    'password'   => generatePassword('123456',rand())
                ];
            }
            // 批量插入
            DB::table('ih_user')->insert($users);
        }
        $this->command->info('用户数据生成完成！');
    }
    
    private function generateChineseFirstName(): string
    {
        $firstNames = ['明', '伟', '芳', '秀英', '娜', '强', '磊', '静', '军', '洋'];
        return $firstNames[array_rand($firstNames)];
    }
    
    private function generateChineseLastName(): string
    {
        $lastNames = ['李', '王', '张', '刘', '陈', '杨', '赵', '黄', '周', '吴'];
        return $lastNames[array_rand($lastNames)];
    }
    
    private function generateUniqueNickname(): string
    {
        $prefix = $this->nicknamePrefixes[array_rand($this->nicknamePrefixes)];
        $suffix = $this->nicknameSuffixes[array_rand($this->nicknameSuffixes)];
        
        return $prefix . $suffix . rand(100, 999);
    }
    
    private function generateUniqueEmail(): string
    {
        $domain = $this->domains[array_rand($this->domains)];
        $randomNumber = rand(1000, 9999);
        
        return getRandChar(6). $randomNumber . '@' . $domain;
    }
}