<?php

namespace LaravelPlugins\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

/**
 * 分布式去重锁
 */
class LockMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param \Closure $genUniqueKey 生成唯一键的闭包
     * @param int $expireMilliseconds 锁过期时间，单位毫秒
     * @param string $connection Redis连接
     * @return mixed
     */
    public function handle($request, Closure $next, Closure $genUniqueKey, $expireMilliseconds = 500, $connection = 'default')
    {
        if (!($genUniqueKey instanceof Closure) || !is_numeric($expireMilliseconds)) {
            throw new \Exception("invalid input parameter");
        }

        //生成键的方法由用户定义，同一个键表示同一个锁
        $key = $genUniqueKey($request);
        $redis = Redis::connection($connection);

        //NX：当键值未设置时才可以设置成功，否则设置不成功（返回nil）。
        //PX：键的过期时间，避免死锁。
        $lockRes = $redis->set($key, '1', 'PX', $expireMilliseconds, 'NX');
        if ($lockRes === null) { //拿不到锁
            throw new \Exception("repeated request");
        }

        $next($request);

        //请求结束后，解锁
        $redis->del($key);

        return;
    }
}
