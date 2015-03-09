# fuelphp-queue
Add original queue system to FuelPHP 1.7.x or later.  
This is FuelPHP package. If you want to use, please use as package.  
How to install : http://fuelphp.com/docs/general/packages.html#/installing

## Preparation
1. Install Orm package.  
This package depends on fuelphp orm package.  
If you don't have orm package, please install before use this.
```
php oil package install orm
```
+ Migrate Database.  
This package need to create task_queue table.  
You must prepare database which supported Fuel\Core\DB classes.  
If you already have database, can migrate by this package.
```
php oil refine migrate --packages=fuelphp-queue
```

## Easy Usage
1. Push queue in your application.
```
\Model_TaskQueue::save_queue(
    "Static::method",
    array($arg1, $arg2 ...)
);
```
+ Run queue by task.
```
php oil refine queue
```
------

## Model_Taskqueue documentation
#### `save_queue($method, $options, $duplicate_type = null)`
Push task queue.

##### String `$method`
Method name that you want to call. Method must be defined static.

##### Array `$options`
Method's arguments.

##### Integer `$duplicate_type`
You can define and set queue type.  
If you run queue in parallel, `$duplicate_type` can set max queue that you execute to each type.  
Please also check the config `queue.duplicate_type` if you use this option.

## Task documentation
#### `php oil refine queues`
Run a queue. Queue is selected by `Model_Taskqueue::pickup()`.  
This task can use in parallel. Also you can set execute limit by `queue.queue_parallel_number`.

#### `php oil refine queues:clean`
Delete old queues that succeeded before than 2 weeks.  
This delete is physical delete.  
If you change this term, please change config `queue.success_queue_delete_term`.

## Config documentation
#### `queue.duplicate_type`
Can set Integer key and Integer value.  
Key is `taks_queues.duplicate_type` value in database table, and value is parallel execute limit.  
If not set value in this config and set value in db value, `Model_TaskQueue::pickup() `throw OutOfRangeException.  

_Notice: You can't set 0 key in this config because 0 used as default value._

#### `queue.zombie_recover_time`
Limitation of queue execute time.  
If queue status stay 'exec' over this time, status is back to 'wait'  
when use `Model_Taskqueue::recover_zombie_queues()` or run next queue task.  
Default value is `-1 hour`.

#### `queue.success_queue_delete_term`
Set the physical delete term.  
when call the task `php oil refine queues:clean`, delete old queues that succeeded before than you set.  
Default value is `-14 days`.

#### `queue.queue_parallel_number`
Number of you can execute task `queues` in Parallel.  
Default value is `3`.

#### `queue.queue_pid_prefix`
Task `queues` are managed by pid file. Pid file is created tmp directory in your application.  
You can set original pid prefix for avoid name confliction.  
Default value is `queue`.

#### `queue.logical_delete`
Logical delete schema setting.  
Default values are above.
* Not delete = `0`
* Deleted = `1`

