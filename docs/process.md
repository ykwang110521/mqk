进程
=====

MQK支持多进程模式运行，具体多进程应用方法查看查看高级章节了解多进程的应用。MQK使用`fork`函数生成指定数量的子进程，对应的PHP函数是
`pcntl_fork`，这种模式只能运行在Linux环境，如果是Windows环境将会自动进入开发模式。开发模式下，会关闭多进程模式，使用单一进程。

工作进程守护
-------------

MQK的主进程在启动子进程后，会监听`SIGCHLD`信号，该信号由操作系统通知主进程子进程退出这一事件。

当主进程得知子进程退出后，会启动新的进程继续当前的工作。在子进程退出的时候，主进程会去reap子进程。reap这个过程是为了防止僵尸
进程产生。没有被reap过的进程会成为僵尸进程，过多的僵尸进程会导致操作系统奔溃。

