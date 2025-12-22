<a id="readme-top"></a>

# Transformer

rule-driven data projection and transformation.

[![GitHub Tag][GitHub Tag]][GitHub Tag URL]
[![Total Downloads][Total Downloads]][Packagist URL]
[![Packagist Version][Packagist Version]][Packagist URL]
[![Packagist PHP Version Support][Packagist PHP Version Support]][Repository URL]
[![Packagist License][Packagist License]][Repository URL]

<!-- TABLE OF CONTENTS -->
<details>
    <summary>Table of Contents</summary>
    <ol>
        <li><a href="#installation">Installation</a></li>
        <li><a href="#usage">Usage</a></li>
        <li><a href="#contributing">Contributing</a></li>
        <li><a href="#contributors">Contributors</a></li>
        <li><a href="#license">License</a></li>
    </ol>
</details>

<!-- INSTALLATION -->

## Installation

You can install the package via [Composer]:

```bash
composer require nacosvel/transformer
```

<p align="right">[<a href="#readme-top">back to top</a>]</p>

<!-- USAGE EXAMPLES -->

### 规则类说明表

| 规则                 | 类型      | 作用                                  | 适用场景                                                                                      |
|--------------------|---------|-------------------------------------|-------------------------------------------------------------------------------------------|
| ConditionRule      | 条件规则    | 根据指定条件过滤、筛选或转换数据，仅当满足条件时执行对应的转换逻辑   | 1. 仅对特定状态（如“已支付”）的数据进行转换；<br>2. 根据字段值范围（如金额>100）调整数据展示；<br>3. 多分支条件下的差异化数据处理              |
| DefaultValueRule   | 默认值规则   | 为字段设置预设默认值，保证数据完整性                  | 1. 未填写的用户昵称默认填充为“匿名用户”；<br>2. 接口返回的空数字字段默认设为0；<br>3. 缺失的时间字段默认填充为当前时间                     |
| FieldMappingRule   | 字段映射规则  | 映射源字段与目标字段的名称/值对应关系，实现字段名重命名或值的静态映射 | 1. 第三方接口字段名（如user_name）映射为本地规范字段（如userName）；<br>2. 枚举值映射（如1→“男”、2→“女”）；<br>3. 多数据源字段统一命名  |
| InvokeRule         | 调用规则    | 调用自定义方法/函数/服务对字段进行动态转换，支持复杂业务逻辑的嵌入  | 1. 对手机号进行脱敏处理（调用脱敏函数）；<br>2. 通过用户ID调用用户服务获取用户名；<br>3. 对金额字段调用汇率转换方法进行币种换算                 |
| MetadataRule       | 源数据规则   | 基于源数据填充                             | --                                                                                        |
| NestedMappingRule  | 嵌套映射规则  | 处理嵌套结构（如对象、数组）的字段映射，支持多层级数据的转换      | 1. 嵌套对象（如user.address）的字段重命名；<br>2. 数组内元素的字段映射（如order.items中的price字段）；<br>3. 多层嵌套数据的扁平化处理 |
| WildcardMapperRule | 通配符映射规则 | 处理数组结构的字段映射，支持多层级数据的转换              | 1. 数组内元素的字段映射；<br>2. 多层嵌套数据的扁平化处理                                                         |

### Rule Class Description Table

| Rule Type          | Function                                                                                                                                                | Applicable Scenarios                                                                                                                                                                                                 |
|--------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| ConditionRule      | Filter, screen or transform data according to specified conditions, and execute the corresponding transformation logic only when the conditions are met | 1. Transform data only for specific status (e.g., "Paid");<br>2. Adjust data display according to field value range (e.g., amount > 100);<br>3. Differentiated data processing under multi-branch conditions         |
| DefaultValueRule   | Set default values for fields to ensure data integrity.                                                                                                 | 1. Default fill "Anonymous User" for unfilled user nicknames;<br>2. Default set empty numeric fields returned by the interface to 0;<br>3. Default fill missing time fields with current time                        |
| FieldMappingRule   | Map the name/value correspondence between source fields and target fields to realize field name renaming or static mapping of values                    | 1. Map third-party interface field names (e.g., user_name) to local standard fields (e.g., userName);<br>2. Enumeration value mapping (e.g., 1→"Male", 2→"Female");<br>3. Unified naming of multi-data source fields |
| InvokeRule         | Call custom methods/functions/services to dynamically transform fields, supporting the embedding of complex business logic                              | 1. Desensitize mobile phone numbers (call desensitization function);<br>2. Get username by calling user service via user ID;<br>3. Convert currency of amount fields by calling exchange rate conversion method      |
| MetadataRule       | Metadata-based population                                                                                                                               | --                                                                                                                                                                                                                   |
| NestedMappingRule  | Handle field mapping of nested structures (e.g., objects, arrays) and support transformation of multi-level data                                        | 1. Field renaming of nested objects (e.g., user.address);<br>2. Field mapping of elements in arrays (e.g., price field in order.items);<br>3. Flattening of multi-level nested data                                  |
| WildcardMapperRule | Handles field mapping in array structures and supports multi-level data transformation.                                                                 | 1. Field mapping of elements within an array;<br>2. Flattening of multi-level nested data                                                                                                                            |

## Usage

```php
$originals = [
    'total_fee'           => 9900,
    'intent'              => 'CAPTURE',
    'purchase_units'      => [
        [
            'reference_id' => 'ORD20250520001',
            'amount'       => [
                'currency_code' => 'USD',
                'value'         => '99.00',
            ],
        ],
    ],
    'application_context' => [
        'return_url' => 'https://example.com/pay/return',
        'cancel_url' => 'https://example.com/pay/notify/paypal',
    ],
];

$targets = [];
```

### `Converter::convert`

```php
$response = Converter::convert($originals, [
    $rule = new FieldMappingRule(
        'total_fee',
        'amount',
        fn($value) => $value / 100
    ),
    $rule = new NestedMappingRule(
        'purchase_units.0.amount.currency_code',
        'payment_code',
    ),
    $rule = new WildcardMapperRule([
        'purchase_units_amount'  => [
            'input'     => 'purchase_units.0.amount.value',
            'transform' => fn($v) => $v * 100,
        ],
        'purchase_units_amounts' => 'purchase_units.*.amount.value',
    ]),
], [
    'default_value' => '1234567890',
]);
```

```json
{
    "default_value": "1234567890",
    "amount": 99,
    "payment_code": "USD",
    "purchase_units_amount": 9900,
    "purchase_units_amounts": [
        99.00
    ]
}
```

### `ConditionRule::class`

```php
use Nacosvel\Transformer\ConditionRule;

$rule = new ConditionRule(
    fn($originals, $targets) => isset($originals['total_fee']),
    function ($originals, $targets) {
        $targets['amount'] = $originals['total_fee'] / 100;
        return $targets;
    }
);
```

```json
{
    "amount": 99
}
```

### `DefaultValueRule::class`

```php
use Nacosvel\Transformer\DefaultValueRule;

$rule = new DefaultValueRule(
    'trade_type', 'JSAPI'
);
```

```json
{
    "trade_type": "JSAPI"
}
```

### `FieldMappingRule::class`

```php
use Nacosvel\Transformer\FieldMappingRule;

$rule = new FieldMappingRule(
    'total_fee',
    'amount',
    fn($value) => $value / 100
);
```

```json
{
    "amount": 99
}
```

### `InvokeRule::class`

```php
use Nacosvel\Transformer\InvokeRule;

$rule = new InvokeRule(function ($originals, $targets) {
    return [
        'amount' => $originals['total_fee'] / 100,
    ];
});
```

```json
{
    "amount": 99
}
```

### `NestedMappingRule::class`

```php
use Nacosvel\Transformer\NestedMappingRule;

$rule = new NestedMappingRule(
    'purchase_units.0.amount.currency_code',
    'code',
);
```

```json
{
    "code": "USD"
}
```

### `WildcardMapperRule::class`

```php
use Nacosvel\Transformer\WildcardMapperRule;

$rule = new WildcardMapperRule([
    'amount'  => [
        'input'     => 'purchase_units.0.amount.value',
        'transform' => fn($v) => $v * 100,
    ],
    'amounts' => 'purchase_units.*.amount.value',
    'default' => [
        'input'     => null,
        'default'   => 'default value',
    ],
]);
```

```json
{
    "amount": 9900,
    "amounts": [
        99.00
    ],
    "default": "default value"
}
```

<!-- CONTRIBUTING -->

## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">[<a href="#readme-top">back to top</a>]</p>

<!-- CONTRIBUTORS -->

## Contributors

Thanks goes to these wonderful people:

<a href="https://github.com/nacosvel/transformer/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=nacosvel/transformer" alt="contrib.rocks image" />
</a>

Contributions of any kind are welcome!

<p align="right">[<a href="#readme-top">back to top</a>]</p>

<!-- LICENSE -->

## License

Distributed under the MIT License (MIT). Please see [License File] for more information.

<p align="right">[<a href="#readme-top">back to top</a>]</p>

[GitHub Tag]: https://img.shields.io/github/v/tag/nacosvel/transformer

[Total Downloads]: https://img.shields.io/packagist/dt/nacosvel/transformer?style=flat-square

[Packagist Version]: https://img.shields.io/packagist/v/nacosvel/transformer

[Packagist PHP Version Support]: https://img.shields.io/packagist/php-v/nacosvel/transformer

[Packagist License]: https://img.shields.io/github/license/nacosvel/transformer

[GitHub Tag URL]: https://github.com/nacosvel/transformer/tags

[Packagist URL]: https://packagist.org/packages/nacosvel/transformer

[Repository URL]: https://github.com/nacosvel/transformer

[GitHub Open Issues]: https://github.com/nacosvel/transformer/issues

[Composer]: https://getcomposer.org

[License File]: https://github.com/nacosvel/transformer/blob/main/LICENSE
