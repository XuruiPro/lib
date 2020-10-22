### 调用方式
~~~
<?php
$get = (new GetFilterData())->filterData([
    "id"   => 12,
    "obj"  => [
        "title" => "测试1"
    ],
    "list" => [
        [
            "id"    => 45,
            "title" => "zg",
            "child" => [
                "map"  => [
                    "id" => "34abc"
                ],
                "list" => [
                    [
                        "title" => "-1 day"
                    ]
                ]
            ]
        ]
    ]
], [
    'page'                      => 'page',
    'limit'                     => 'limit',
    'id'                        => 'int',
    'title'                     => 'string',
    'obj'                       => 'array',
    'obj.id'                    => 'int',
    'obj.title'                 => 'string',
    'list'                      => 'array',
    'list.*'                    => 'array',
    'list.*.id'                 => 'int',
    'list.*.title'              => 'string',
    'list.*.child'              => 'array',
    'list.*.child.map'          => 'array',
    'list.*.child.map.id'       => 'int',
    'list.*.child.list'         => 'array',
    'list.*.child.list.*'       => 'array',
    'list.*.child.list.*.title' => 'date:Y-m-d H:i:s',
]);
~~~
### 获取内容
~~~
[
    "page"  => 1,
    "limit" => 10,
    "id"    => 12,
    "title" => "",
    "obj"   => [
        "id"    => 0,
        "title" => "测试1"
    ],
    "list"  => [
        [
            "id"    => 45,
            "title" => "zg",
            "child" => [
                "map"  => [
                    "id" => 34
                ],
                "list" => [
                    [
                        "title" => "2020-10-21 15:49:06"
                    ]
                ]
            ]
        ]
    ]
]
~~~