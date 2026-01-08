# 此项目是用于秒杀活动中后台基本实现
`
## 扣减库存 Lua 脚本
local script_decrease = [[
    local activity_id = KEYS[1]
    local user_id = KEYS[2]
    local quantity = tonumber(ARGV[1])
    -- 库存 key
    local inventory_key = "seckill:inventory:" .. activity_id
    local user_limit_key = "seckill:user_limit:" .. activity_id .. ":" .. user_id
    local limit_per_user = tonumber(ARGV[2])
    -- 检查用户购买限制
    local user_bought = redis.call('GET', user_limit_key) or 0
    if tonumber(user_bought) + quantity > limit_per_user then
        return {0, "超过购买限制"}
    end  
    -- 检查库存
    local current_stock = redis.call('GET', inventory_key)
    if not current_stock then
        return {0, "活动不存在或已结束"}
    end 
    current_stock = tonumber(current_stock)
    if current_stock < quantity then
        return {0, "库存不足"}
    end
    -- 扣减库存
    local new_stock = current_stock - quantity
    redis.call('SET', inventory_key, new_stock)
    -- 更新用户购买次数
    redis.call('INCRBY', user_limit_key, quantity)
    redis.call('EXPIRE', user_limit_key, 86400) -- 24小时过期
    -- 记录已售数量
    redis.call('INCRBY', "seckill:sold:" .. activity_id, quantity)
    return {1, new_stock}
]]
## 获取秒杀令牌
local script_get_token = [[
    local activity_id = KEYS[1]
    local user_id = KEYS[2]
    local token = ARGV[1]
    local expire_time = ARGV[2]
    
    local token_key = "seckill:token:" .. activity_id .. ":" .. token
    local user_token_key = "seckill:user_token:" .. activity_id .. ":" .. user_id
    
    -- 检查是否已有令牌
    local existing_token = redis.call('GET', user_token_key)
    if existing_token then
        return {0, "已有令牌: " .. existing_token}
    end
    
    -- 生成新令牌
    redis.call('SETEX', token_key, expire_time, user_id)
    redis.call('SETEX', user_token_key, expire_time, token)
    
    return {1, token}
]]
`
# 秒杀系统性能优化检查清单

## ✅ 基础设施
- [ ] Redis 集群部署（主从+哨兵）
- [ ] MySQL 读写分离
- [ ] CDN 静态资源加速
- [ ] 负载均衡配置

## ✅ 代码优化
- [ ] 热点数据缓存
- [ ] 异步非阻塞处理
- [ ] 连接池配置优化
- [ ] SQL 语句优化

## ✅ 安全防护
- [ ] DDoS 防护
- [ ] CC 攻击防护
- [ ] 接口防刷
- [ ] 数据加密

## ✅ 监控告警
- [ ] 关键指标监控
- [ ] 日志收集分析
- [ ] 实时告警系统
- [ ] 性能分析工具

## ✅ 容灾备份
- [ ] 数据备份策略
- [ ] 故障转移方案
- [ ] 降级策略
- [ ] 限流熔断

# nginx 配置
`
# nginx.conf
upstream seckill_backend {
    least_conn;
    server seckill-api-1:8080 max_fails=3 fail_timeout=30s;
    server seckill-api-2:8080 max_fails=3 fail_timeout=30s;
    server seckill-api-3:8080 max_fails=3 fail_timeout=30s;
    # ... 更多实例
}

server {
    listen 80;
    server_name seckill.example.com;
    
    # 限制连接数
    limit_conn_zone $binary_remote_addr zone=perip:10m;
    limit_conn_zone $server_name zone=perserver:10m;
    
    # 限制请求速率
    limit_req_zone $binary_remote_addr zone=ratelimit:10m rate=10r/s;
    
    location / {
        # 应用限流
        limit_conn perip 10;
        limit_conn perserver 1000;
        limit_req zone=ratelimit burst=20 nodelay;
        
        # 反向代理
        proxy_pass http://seckill_backend;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        
        # 超时设置
        proxy_connect_timeout 3s;
        proxy_read_timeout 10s;
        proxy_send_timeout 10s;
    }
    
    # 静态资源缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
`
# redis相关键
// 库存相关
$inventoryKey = "seckill:inventory:{activity_id}";            // 库存数量
$inventorySoldKey = "seckill:sold:{activity_id}";             // 已售数量
$inventoryLockKey = "seckill:inventory_lock:{activity_id}";   // 库存锁

// 用户相关
$userOrderKey = "seckill:user_order:{activity_id}:{user_id}"; // 用户订单
$userLimitKey = "seckill:user_limit:{activity_id}:{user_id}"; // 用户购买次数
$userBlacklistKey = "seckill:blacklist:{user_id}";           // 用户黑名单

// 令牌相关
$tokenKey = "seckill:token:{token}";                        // 令牌验证
$tokenQueueKey = "seckill:token_queue:{activity_id}";       // 令牌队列

// 队列相关
$orderQueueKey = "queue:seckill_order";                     // 订单队列
$paymentQueueKey = "queue:seckill_payment";                 // 支付队列

# 架构
┌─────────────────────────────────────────────────────────────┐
│                    客户端层 (Client Layer)                    │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐      │
│  │   Web    │ │   H5     │ │   App    │ │  小程序  │      │
│  └──────────┘ └──────────┘ ┌──────────┘ └──────────┘      │
└─────────────────────┬───────────────────────────────────────┘
                      │ HTTP/HTTPS
┌─────────────────────▼───────────────────────────────────────┐
│                    网关层 (Gateway Layer)                     │
│  ┌─────────────────────────────────────────────────────┐  │
│  │            Nginx/OpenResty + Lua脚本                 │  │
│  └─────────────────────────────────────────────────────┘  │
│ 功能：限流、恶意请求拦截、静态资源缓存                      │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                  业务服务层 (Service Layer)                   │
│  ┌────────────┐ ┌────────────┐ ┌────────────┐            │
│  │ 用户服务   │ │ 商品服务   │ │ 订单服务   │            │
│  └────────────┘ └────────────┘ └────────────┘            │
│  ┌────────────┐ ┌────────────┐                           │
│  │ 秒杀服务   │ │ 支付服务   │                           │
│  └────────────┘ └────────────┘                           │
└─────────────────────┬───────────────────────────────────────┘
                      │ RPC/Dubbo/gRPC
┌─────────────────────▼───────────────────────────────────────┐
│                 数据存储层 (Storage Layer)                    │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐      │
│  │ MySQL    │ │ Redis    │ │ MongoDB  │ │   ES     │      │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘      │
└─────────────────────────────────────────────────────────────┘

