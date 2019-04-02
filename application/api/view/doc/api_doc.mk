目录

1\. 获得网关列表

2\. 获得设备列表

3\. 获得任务列表

4\. 获得生产统计量

5\. 获得生产图表数据

6\. 获得每条产线的有效运行时间

---

**1\. 获得网关列表**
##### 接口功能
> 获取当前用户的网关列表

##### URL
> [http://shop.zuoduoduo.cn/api/smtreport/get_gateways](http://shop.zuoduoduo.cn/api/smtreport/get_gateways)

##### 支持格式
> URL

##### HTTP请求方式
> GET

##### 请求参数
> 无

##### 返回字段
> |返回字段|字段类型|说明                                   |
|:-----         |:------  |:-----------------------------   |
|gateway_list   |array    |网关清单,包含网关所属的产线名称（一个网关可以属于多条产线）|
|runtime        |int      |接口处理时间                     |
|ErrorCode  	|int 	  |结果状态。0：正常；1：错误     |
|ErrorMsg 	|string   |结果信息                         |

##### 接口示例
> 地址：
[http://shop.zuoduoduo.cn/api/smtreport/get_gateways](http://shop.zuoduoduo.cn/api/smtreport/get_gateways)

> 返回：
{<br/>
  "gateway_list":[{"id":"zdd-2018112204","line":["\u4ea7\u7ebf2","\u672a\u77e5\u4ea7\u7ebf"]},{"id":"zdd-2018112205","line":["\u4ea7\u7ebf3"]}],<br/>
  "runtime":441,<br/>
  "ErrorCode":0,<br/>
  "ErrorMsg":"OK"<br/>
}
<br/><br/>


**2\. 获得设备列表**
##### 接口功能
> 获取当前用户的设备列表

##### URL
> [http://shop.zuoduoduo.cn/api/smtreport/get_devices](http://shop.zuoduoduo.cn/api/smtreport/get_devices)

##### 支持格式
> URL

##### HTTP请求方式
> GET

##### 请求参数
> 无

##### 返回字段
> |返回字段|字段类型|说明                                   |
|:-----         |:------  |:-----------------------------  |
|device_list    |array    |设备清单                        |
|runtime        |int      |接口处理时间                    |
|ErrorCode      |int      |结果状态。0：正常；1：错误。     |
|ErrorMsg       |string   |结果信息                        |

##### 接口示例
> 地址：
[http://shop.zuoduoduo.cn/api/smtreport/get_devices](http://shop.zuoduoduo.cn/api/smtreport/get_devices)

> 返回：
{<br/>
  "device_list":[{"gateway_id":"zdd-2018112204","model_name":"JUKI_KE_2050M","sub_id":1,"online_status":1,"line":"产线2"},{"gateway_id":"zdd-2018112204","model_name":"JUKI_KE_3010AL","sub_id":3,"online_status":0,"line":"未知产线"}],<br/>
  "runtime":441,<br/>
  "ErrorCode":0,<br/>
  "ErrorMsg":"OK"<br/>
}
<br/><br/>


**3\. 获得任务列表**
##### 接口功能
> 获取当前用户的设备列表，可以指定一个生产的（注意不是任务创建的）时间区间

##### URL
> [http://shop.zuoduoduo.cn/api/smtreport/get_tasks](http://shop.zuoduoduo.cn/api/smtreport/get_tasks)

##### 支持格式
> URL

##### HTTP请求方式
> GET

##### 请求参数
> |参数|必选|类型|说明|
|:-----      |:-------|:-----|-----                                  |
|page        |false   |int   |请求的页码数，若不给则默认为0。小于等于0时，表示不分页，返回所有查询到的结果  |
|start_time  |false   |int   |生产区间的开始时间                      |
|end_time    |false   |int   |生产区间的结束时间                      |

##### 返回字段
> |返回字段|字段类型|说明                                   |
|:-----         |:------  |:-----------------------------   |
|task_list      |array    |设备清单                         |
|page_number    |int      |查询的页码			    |
|runtime        |int      |接口处理时间                     |
|ErrorCode      |int      |结果状态。0：正常；1：错误。      |
|ErrorMsg       |string   |结果信息                         |

##### 接口示例
> 地址：
[http://shop.zuoduoduo.cn/api/smtreport/get_tasks](http://shop.zuoduoduo.cn/api/smtreport/get_tasks)

> 返回：
{<br/>
  "task_list":[{"task_id":25,"gateway_id":"zdd-2018112208","sub_id":4,"task_name":"C:\\PRG\\P5-3232-16S-V1.0.E45","create_time":"2018-12-04 11:02:23","line":"产线2"},{"task_id":26,"gateway_id":"zdd-2018112208","sub_id":5,"task_name":"C:\\PRG\\P5-3232-16S-V1.0.E45","create_time":"2018-12-04 11:02:24","line":"产线1"}],<br/>
  "page_number":1,<br/>
  "runtime":441,<br/>
  "ErrorCode":0,<br/>
  "ErrorMsg":"OK"<br/>
}
<br/><br/>


**4\. 获得生产统计量**
##### 接口功能
> 获取当前用户在给定时间段内的统计量，同时可以用给定的条件进行灵活的查询：网关ID、设备子ID、任务ID

##### URL
> [http://shop.zuoduoduo.cn/api/smtreport/get_smt_count](http://shop.zuoduoduo.cn/api/smtreport/get_smt_count)

##### 支持格式
> URL

##### HTTP请求方式
> GET

##### 请求参数
> |参数|必选|类型|说明|
|:-----  |:-------|:-----|-----                                   |
|start_time    |true    |int    |查询的开始时间点                  |
|end_time      |true    |int    |查询的结束时间点                  |
|gateway_id    |false   |string |网关ID，若为空则查询所有网关                           |
|device_sub_id |false   |int    |设备子ID，若为空则查询网关下的所有设备. 如果gateway_id为空, 则该参数会被忽略           |
|task_id       |false   |int    |任务ID，若gateway_id和device_sub_id都为空，则该参数会被忽略                           |

> 注：基板总数和电路总数的统计量，和gateway_id、device_sub_id、task_id都没有直接关联，这些数据只和生产线相关

##### 返回字段
> |返回字段               |字段类型  |说明                             |
|:-----                   |:------  |:-----------------------------   |
|smt_count                |int      |贴片总数                          |
|device_smt_count         |array    |每台设备的贴片总数，设备标识符由“网关ID-设备子ID”表示   |
|task_smt_count           |array    |每个任务的贴片总数                |
|base_boardcount          |int      |基板总数			      |
|device_base_boardcount   |array    |每台网关的基板总数		      |
|task_base_boardcount     |array    |每个任务的基板总数，设备标识符由“网关ID-设备子ID”表示   |
|line_base_boardcount     |array    |每条产线的基板总数                |
|boardcount               |int      |电路总数			      |
|gateway_boardcount       |array    |每个网关的电路总数	              |
|task_boardcount          |array    |每个任务的电路总数	              |
|line_boardcount          |array    |每条产线的电路总数	              |
|is_abnormal_detected     |array    |每条产线的数据统计是否可能有异常   |
|runtime                  |int      |接口处理时间                      |
|ErrorCode                |int      |结果状态。0：正常；1：错误。       |
|ErrorMsg                 |string   |结果信息                          |

##### 接口示例
> 地址：
[http://shop.zuoduoduo.cn/api/smtreport/get_smt_count/start_time/1545408000/end_time/1545444649](http://shop.zuoduoduo.cn/api/smtreport/get_smt_count/start_time/1545408000/end_time/1545444649)

> 返回：
{<br/>
  "smt_count":177513,<br/>
  "gateway_smt_count":{"zdd-2018112204":0,"zdd-2018112205":15598,"zdd-2018112206":19200,"zdd-2018112207":28578,"zdd-2018112208":99590},<br/>
  "task_smt_count":[{"id":393,"name":"D:\\PRG\\\u534e\u5efa\u5b89-\u7ea2\u677f\\\u7ea2\u677f20DV2-MB-V3.4(\u5355\u5361+\u786c\u76d8+\u53cc\u5361)-BOT(4-4).E48","count":24447},{"id":401,"name":"D:\\PRG\\\u534e\u5efa\u5b89-\u7ea2\u677f\\\u7ea2\u677f20DV2-MB-V3.4(\u5355\u5361+\u786c\u76d8+\u53cc\u5361)-TOP(4-3).E48","count":11473}],<br/>
  "base_boardcount":1320,<br/>
  "gateway_base_boardcount":{"zdd-2018112207":440,"zdd-2018112205":716,"zdd-2018112210":0,"zdd-2018112204":0},<br/>
  "task_base_boardcount":[{"id":388,"name":"D:\\PRG\\\u534e\u5efa\u5b89-\u7ea2\u677f\\\u7ea2\u677f20DV2-MB-V3.4(\u5355\u5361+\u786c\u76d8+\u53cc\u5361)-BOT(4-2).x11","count":255},{"id":405,"name":"D:\\PRG\\\u534e\u5efa\u5b89-\u7ea2\u677f\\\u7ea2\u677f20DV2-MB-V3.4(\u5355\u5361+\u786c\u76d8+\u53cc\u5361)-TOP(4-2).x11","count":48}],<br/>
  "line_base_boardcount":"\u4ea7\u7ebf2":115,"\u4ea7\u7ebf3":55,"\u4ea7\u7ebf1":88},<br/>
  "boardcount":4842,<br/>
  "gateway_boardcount":[{"id":388,"name":"D:\\PRG\\\u534e\u5efa\u5b89-\u7ea2\u677f\\\u7ea2\u677f20DV2-MB-V3.4(\u5355\u5361+\u786c\u76d8+\u53cc\u5361)-BOT(4-2).x11","count":1006},{"id":405,"name":"D:\\PRG\\\u534e\u5efa\u5b89-\u7ea2\u677f\\\u7ea2\u677f20DV2-MB-V3.4(\u5355\u5361+\u786c\u76d8+\u53cc\u5361)-TOP(4-2).x11","count":192}],<br/>
  "task_boardcount":{"279":0,"293":200,"295":912,"290":866,"8":928,"285":736,"296":1200},<br/>
  "line_boardcount":{"\u4ea7\u7ebf2":690,"\u4ea7\u7ebf3":55,"\u4ea7\u7ebf1":1408},<br/>
  "is_abnormal_detected":{"\u4ea7\u7ebf2":false,"\u4ea7\u7ebf3":false,"\u4ea7\u7ebf1":false},<br/>
  "runtime":5007,<br/>
  "ErrorCode":0,<br/>
  "ErrorMsg":"OK"<br/>
}
<br/><br/>


**5\. 获得生产图表数据**
##### 接口功能
> 获取当前用户在给定时间段内的图表数据，同时可以用给定的条件进行灵活的查询：网关ID、设备子ID、任务ID

##### URL
> [http://shop.zuoduoduo.cn/api/smtreport/get_smt_histogram](http://shop.zuoduoduo.cn/api/smtreport/get_smt_histogram)

##### 支持格式
> URL

##### HTTP请求方式
> GET

##### 请求参数
> |参数|必选|类型|说明|
|:-----  |:-------|:-----|-----                                   |
|start_time    |true    |int    |查询的开始时间点                  |
|end_time      |true    |int    |查询的结束时间点                  |
|gateway_id    |false   |string |网关ID，若为空则查询所有网关                           |
|device_sub_id |false   |int    |设备子ID，若为空则查询网关下的所有设备. 如果gateway_id为空, 则该参数会被忽略                         |
|task_id       |false   |int    |任务ID，若gateway_id和device_sub_id都为空，则该参数会被忽略                           |

##### 返回字段
> |返回字段|字段类型|说明                                   |
|:-----         |:------  |:-----------------------------   |
|smt_data       |array    |每个任务的数据列表                         |
|runtime        |int      |接口处理时间                     |
|ErrorCode      |int      |结果状态。0：正常；1：错误。      |
|ErrorMsg       |string   |结果信息                         |

##### 接口示例
> 地址：
[http://shop.zuoduoduo.cn/api/smtreport/get_smt_histogram/start_time/1545235200/end_time/1545290573](http://shop.zuoduoduo.cn/api/smtreport/get_smt_histogram/start_time/1545235200/end_time/1545290573)

> 返回：
{<br/>
  "smt_data":[{"gateway_id":"zdd-2018112206","device_sub_id":1,"task_id":258,"task_name":"D:\\Prg\\HRWL\\HRWL-M330-V1.7(2-1).s01x","data":[[1545235228,73]]},{"gateway_id":"zdd-2018112207","device_sub_id":1,"task_id":233,"task_name":"D:\\PRG\\\u5de5\u63a7\u677f\\HD V03(\u666e\u901a\u7248)-TOP( 4--2).x11","data":[[1545235261,6576]]}],<br/>
  "runtime":3483,<br/>
  "ErrorCode":0,<br/>
  "ErrorMsg":"OK"<br/>
}
<br/><br/>


**6\. 获得每条产线的有效运行时间**
##### 接口功能
> 获取用户每条产线的有效运行时间

##### URL
> [http://shop.zuoduoduo.cn/api/smtreport/get_effective_time](http://shop.zuoduoduo.cn/api/smtreport/get_effective_time)

##### 支持格式
> URL

##### HTTP请求方式
> GET

##### 请求参数
> |参数|必选|类型|说明|
|:-----  |:-------|:-----|-----                                   |
|start_time    |true    |int    |查询的开始时间点                  |
|end_time      |true    |int    |查询的结束时间点                  |

##### 返回字段
> |返回字段|字段类型|说明                                   |
|:-----         |:------  |:-----------------------------  |
|effective_time |array    |每条产线的有效运行时间           |
|runtime        |int      |接口处理时间                     |
|ErrorCode      |int      |结果状态。0：正常；1：错误。      |
|ErrorMsg       |string   |结果信息                         |

##### 接口示例
> 地址：
[http://shop.zuoduoduo.cn/api/smtreport/get_effective_time/start_time/1545235200/end_time/1545298951](http://shop.zuoduoduo.cn/api/smtreport/get_effective_time/start_time/1545235200/end_time/1545298951)

> 返回：
{<br/>
  "effective_time":[{"line_name":"\u4ea7\u7ebf1","effective_time":17931},{"line_name":"\u4ea7\u7ebf2","effective_time":22503},{"line_name":"\u4ea7\u7ebf3","effective_time":34106}],<br/>
  "runtime":1717,<br/>
  "ErrorCode":0,<br/>
  "ErrorMsg":"OK"<br/>
}
<br/><br/>



