<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use App\Services\LockService;

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

        $lockRes = LockService::lock($key, $expireMilliseconds, $connection);
        if ($lockRes === null) { //拿不到锁
            throw new \Exception("repeated request");
        }

        $next($request);

        //请求结束后，解锁
        LockService::unlock($key, $connection);

        return;
    }
}
