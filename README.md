# Typecho 文章静态化缓存插件 TpHtmlCache

## 插件简介

没钱买数据库，还在用着阿里最渣档次的ecs，得想办法优化下加载速度先<br>
<br>
激活插件后所有文章会在本地以静态页的方式被缓存掉<br>
写的比较急，功能还比较简单，还没写后台和设置，就这样先把,剩下后面慢慢再完善成一个插件<br>

## 安装方法

1. 到https://github.com/huhaku/typecho_TpHtmlCache 项目中下载最新版本的文件。<br>
2. 将下载的压缩包进行解压，重命名为`TpHtmlCache`,修改66行的缓存时间（$expire）105行的需要缓存的路径(/posts/),并上传至`Typecho`插件目录中。<br>
3. 后台激活插件。<br>

已测试可用typecho版本 1.1 (17.10.30)<br>

## 注意

1. cache目录必须为可写

