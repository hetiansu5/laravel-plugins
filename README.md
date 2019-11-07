## 简介
本项目旨在提供一些Laravel框架的小工具插件。  
本项目不提供composer包引入机制，主要是因为考虑到本身有些处理规需要用户结合自身项目使用习惯，这里只提供代码实现。

## Middleware
- LockMiddleware: 分布式请求锁中间件，可以确保单位时间内，某个粒度的请求只会被处理一个，重复的会被过滤掉。

## Services
- LockService: 分布式锁组件