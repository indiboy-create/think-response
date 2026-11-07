<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 失败响应的 HTTP 状态码策略
    |--------------------------------------------------------------------------
    | false：严格模式，取业务码的前三位作为 HTTP 状态码，例如 500102 => 500、404 => 404
    | 200  ：所有失败响应都返回 HTTP 200，业务状态由 status 和 code 字段体现
    | 500  ：所有失败响应都返回 HTTP 500
    */
    'error_code' => false,

    /*
    |--------------------------------------------------------------------------
    | 响应字段
    |--------------------------------------------------------------------------
    | show  ：是否输出该字段
    | alias ：输出时使用的字段名，支持点路径
    |
    | 例如把 message 改为 msg：
    | 'message' => ['show' => true, 'alias' => 'msg']
    |
    | 例如把分页的内层 data 改为 list：
    | 'data.data' => ['show' => true, 'alias' => 'data.list']
    |
    | 注意：修改顶层字段的 alias 时，嵌套字段的点路径也要同步修改。
    */
    'fields' => [
        'status'    => ['show' => true, 'alias' => 'status'],
        'code'      => ['show' => true, 'alias' => 'code'],
        'message'   => ['show' => true, 'alias' => 'message'],
        'data'      => ['show' => true, 'alias' => 'data'],
        'error'     => ['show' => true, 'alias' => 'error'],
        'data.data' => ['show' => true, 'alias' => 'data.data'],
        'data.meta' => ['show' => true, 'alias' => 'data.meta'],
    ],

    /*
    |--------------------------------------------------------------------------
    | 分页数据字段名
    |--------------------------------------------------------------------------
    | 分页数据会输出为 [paginate_key => 列表数据, 'meta' => ['pagination' => [...]]]
    | 默认 data 表示 data.data，可改为 rows、list 等
    | 修改此项后，fields 中的 data.data、data.meta 需要同步改为 data.{paginate_key}、data.meta
    */
    'paginate_key' => 'data',

    /*
    |--------------------------------------------------------------------------
    | 业务码对应的消息
    |--------------------------------------------------------------------------
    | 键为业务码或 HTTP 状态码，未指定 message 时按以下优先级读取：
    | 1. 调用方法时显式传入的 message
    | 2. 语言包中的消息（见 lang 配置）
    | 3. 此处配置的消息
    | 4. 内置的 HTTP 状态码中文描述
    */
    'messages' => [
        200 => '操作成功',
        500 => '操作失败',
    ],

    /*
    |--------------------------------------------------------------------------
    | 语言包名称
    |--------------------------------------------------------------------------
    | ThinkPHP 8 的应用语言包位于 app/lang/{langSet}.php 或 app/lang/{langSet}.response.php
    |
    | 平面书写（推荐，无需修改 lang 配置）：
    | app/lang/zh-cn.php 返回 ['response_200101' => '注册成功']
    |
    | 分组书写（需要 config/lang.php 中 allow_group 为 true）：
    | app/lang/zh-cn.php 返回 ['response' => ['200101' => '注册成功']]
    |
    | 也可以直接使用业务码作为键名，例如 200101 => '注册成功'
    | 设为空字符串表示不读取语言包
    */
    'lang' => 'response',

    /*
    |--------------------------------------------------------------------------
    | 默认消息
    |--------------------------------------------------------------------------
    | 业务码和 HTTP 状态码都找不到对应消息时使用
    */
    'default_message' => [
        'success' => '操作成功',
        'fail'    => '操作失败',
    ],

    /*
    |--------------------------------------------------------------------------
    | 异常响应
    |--------------------------------------------------------------------------
    | enable   ：是否启用内置异常处理（仅当应用未自定义异常处理器时生效）
    | override ：是否强制替换应用自定义的 think\exception\Handle 绑定
    | always   ：是否所有请求都返回 JSON 异常响应
    |            false 时仅对期望 JSON 的请求生效（Accept 含 json、AJAX 或 JSON 请求体）
    | debug    ：是否在响应中显示异常原始消息，null 表示跟随 app.debug
    | map      ：异常类 => [code, message, expose]
    |            code   ：业务码，前三位同时决定 HTTP 状态码
    |            message：对外显示的消息
    |            expose ：是否显示异常自身的消息（默认 false）
    */
    'exception' => [
        'enable'   => true,
        'override' => false,
        'always'   => false,
        'debug'    => null,
        'map'      => [
            \think\exception\ValidateException::class         => [
                'code'    => 422,
                'message' => '验证失败',
                'expose'  => true,
            ],
            \think\db\exception\ModelNotFoundException::class => [
                'code'    => 404,
                'message' => '数据不存在',
                'expose'  => false,
            ],
            \think\db\exception\DataNotFoundException::class  => [
                'code'    => 404,
                'message' => '数据不存在',
                'expose'  => false,
            ],
        ],
    ],
];