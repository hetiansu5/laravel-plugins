## 简介
本项目旨在提供一些Laravel框架的小工具插件。  
本项目不提供composer包引入机制，主要是因为考虑到本身有些处理规需要用户结合自身项目使用习惯，这里只提供代码实现。

## Middleware
- DuplicateRequestFilterMiddleware: 重复请求过滤中间件，用户可自定义重复请求的规则，在锁的有效期内，某个粒度的请求只能处理一次，其他被过滤掉。需要注意锁过期后，程序还未处理完毕，如果有同个粒度的请求进来，也是会被处理的。

## Services
- DistributedLockService: 分布式锁组件

## Utils
- StrUtil: 过滤非UTF-8字符串