<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\DistributedLockService;

/**
 * 重复请求过滤 -- 基于分布式锁
 *
 * @rule 重复规则由用户自定义
 */
class DuplicateRequestFilterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $genKeyMethod 生成唯一键的方法
     * @param int $expireMilliseconds 锁过期时间，单位毫秒。
     * @param string $connection Redis连接
     * @return mixed
     */
    public function handle($request, Closure $next, $genKeyMethod, $expireMilliseconds = 1000, $connection = 'default')
    {
        if (!(is_callable([$this, $genKeyMethod]) && is_numeric($expireMilliseconds))) {
            throw new \Exception("input parameter error");
        }

        //生成键的方法由用户定义，同一个键表示同一个锁
        $key = call_user_func([$this, $genKeyMethod, $request]);

        $lockRes = DistributedLockService::lock($key, $expireMilliseconds, $connection);
        if ($lockRes === null) { //拿不到锁
            throw new \Exception("duplicate request");
        }

        $response = $next($request);

        //请求结束后，解锁
        DistributedLockService::unlock($key, $connection);

        return $response;
    }

    /**
     * 可自定义去重粒度规则
     *
     * @param $request
     * @return string
     */
    protected function key($request)
    {
        return $request->input('userId');
    }

}
