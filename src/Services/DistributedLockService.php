<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

/**
 * 分布式锁
 */
class DistributedLockService
{

    /**
     * 即刻获取锁
     * @desc 和多线程的加锁有点不一样，因为PHP的并发是多进程，如果没有拿到锁，表示锁被其他进程拿到了，需要根据业务做相应逻辑处理，是循环等待获取，还是丢弃任务
     *
     * @param string $key
     * @param int $milliseconds 锁的有效时间，单位为毫秒
     * @param string $connection Redis连接
     * @return null|\Predis\Response\Status null表示没有拿到锁  {return}->getPayload()== "OK"表示拿到锁
     */
    public static function lock($key, $milliseconds, $connection = 'default')
    {
        $redis = Redis::connection($connection);
        //NX：当键值未设置时才可以设置成功，否则设置不成功（返回nil）。
        //PX：键的过期时间，避免死锁。
        return $redis->set($key, '1', 'PX', $milliseconds, 'NX');
    }

    /**
     * 解锁
     *
     * @param string $key
     * @param string $connection Redis连接
     * @return void
     */
    public static function unlock($key, $connection = 'default')
    {
        $redis = Redis::connection($connection);
        $redis->del($key);
    }

    /**
     * 循环重试获取到锁
     * @desc 循环机制为设置时为每100毫秒循环一次(如果有效时长小于100毫秒，则使用有效时长)，并不适合单个粒度数据高并发的请求，主要是为了解决并发请求的数据串行处理。
     * @desc 一般来说，比如打粒度细化到用户，单个用户并发非常高的情况是比较少的，除非是恶意用户刷接口。但是比较常见的情况是，突然之间用户并发了几个请求过来，如果不串行
     * @desc 处理的话，后端数据库可能会处理异常。
     *
     * @param string $key
     * @param int $milliseconds 锁的有效时间，单位为毫秒
     * @param string $connection Redis连接
     * @return bool
     */
    public static function lockWait($key, $milliseconds, $connection = 'default')
    {
        $lockRes = self::lock($key, $milliseconds, $connection);
        if ($lockRes !== null) {
            return true;
        }
        $sleep = 100;
        if ($milliseconds < 100) {
            $sleep = $milliseconds;
        }
        usleep($sleep * 1000);
        return self::lockWait($key, $milliseconds, $connection);
    }

}