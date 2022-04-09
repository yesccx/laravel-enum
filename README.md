<h1 align="center">Laravel-Enum</h1>
<p align="center">简单易用的枚举类实现，通过枚举类统一管理枚举值</p>

<p align="center"><a href="https://github.com/yesccx/laravel-enum"><img alt="For Laravel 5" src="https://img.shields.io/badge/laravel-9.*-green.svg" style="max-width:100%;"></a>
<a href="https://packagist.org/packages/yesccx/laravel-enum"><img alt="Latest Stable Version" src="https://img.shields.io/packagist/v/yesccx/laravel-enum.svg" style="max-width:100%;"></a>
<a href="https://packagist.org/packages/yesccx/laravel-enum"><img alt="Latest Unstable Version" src="https://img.shields.io/packagist/vpre/yesccx/laravel-enum.svg" style="max-width:100%;"></a>
<a href="https://packagist.org/packages/yesccx/laravel-enum"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/yesccx/laravel-enum.svg?maxAge=2592000" style="max-width:100%;"></a>
<a href="https://packagist.org/packages/yesccx/laravel-enum"><img alt="License" src="https://img.shields.io/packagist/l/yesccx/laravel-enum.svg?maxAge=2592000" style="max-width:100%;"></a></p>

## 目录
- [目录](#目录)
- [功能特点](#功能特点)
- [开始使用](#开始使用)
  - [安装](#安装)
  - [基础用法](#基础用法)
    - [定义枚举类](#定义枚举类)
    - [利用枚举类进行值判断/赋值](#利用枚举类进行值判断赋值)
    - [判断值是否合法](#判断值是否合法)
    - [使用验证规则判断值是否合法](#使用验证规则判断值是否合法)
    - [获取枚举值的含义说明](#获取枚举值的含义说明)
    - [使用模型访问器定义字段含义说明](#使用模型访问器定义字段含义说明)
    - [获取某个字段的所有可选项](#获取某个字段的所有可选项)
  - [进阶用法](#进阶用法)
    - [利用PHP8.1注解定义枚举类](#利用php81注解定义枚举类)
    - [复用枚举值](#复用枚举值)
- [使用建议](#使用建议)
- [TODO](#todo)
- [License](#license)

## 功能特点

- 支持 `Laravel 9+`
- 统一管理表或对象的可枚举字段，在任意处利用枚举类对值进行合法性校验
- 内置的枚举值 表单验证规则
- 利用预定义的映射关系，解析出枚举值对应的含义说明
- 使用 `PHP8.1` 的注解功能快速定义枚举类

## 开始使用

### 安装

| 运行环境要求     |
| ------------ |
| PHP ^8.1.0   |
| Laravel Framework ^9.0 |

```shell
composer require yesccx/laravel-enum
```

### 基础用法

#### 定义枚举类

所有枚举类需要继承基类 `Yesccx\LaravelEnum\Enum` ，同时可以选择性的重写 `loadColumnMap` 方法，在该方法中定义字段与其对应字段值的映射关系，从而为后续的一些业务场景中提供便捷的查询及判断功能，同时也方便一目了然的查看某个模型(表)中有哪些枚举字段。

``` php
<?php

namespace App\Enums;

use Yesccx\LaravelEnum\Enum;

final class UserEnum extends Enum
{
    // 状态: 禁用
    public const STATUS__OFF = 0;

    // 状态: 启用
    public const STATUS__ON = 1;

    /* ------------------------------ */

    // 性别: 男
    public const GENDER__MAN = 1;

    // 性别: 女
    public const GENDER__WOMAN = 2;

    /* ------------------------------ */

    /**
     * 字段与字段值的映射关系
     *
     * @return array
     */
    protected function loadColumnMap(): array
    {
        return [
            'status' => [
                self::STATUS__OFF => '禁用',
                self::STATUS__ON  => '启用',
            ]
            'gender' => [
                self::GENDER__MAN    => '男',
                self::GENDER__WOMAN  => '女',
            ]
        ];
    }
}
```

#### 利用枚举类进行值判断/赋值

``` php
use App\Enums\UserEnum;

// 判断字段值是否符合预期
if ($user->status != UserEnum::STATUS__ON) {
    throw new \Exception('用户已被禁用！');
}


// 使用枚举值对其它变量进行赋值
$result = $user->update([
    'status' => UserEnum::STATUS__ON
]);
if (empty($result)) {
    throw new \Exception('更新失败');
}
```

#### 判断值是否合法

实例化枚举类时指定 `字段名`，再通过实例对象上的 `has` 方法判断某个值是否在这个字段的有效值范围内。

``` php
use App\Enums\UserEnum;

$status = (int) request()->get('status', 0);

if (!(new UserEnum('status'))->has($status)) {
    throw new \Exception('状态值不合法');
}

// 或者通过make方法快速初始化枚举类
if (!UserEnum::make('status')->has($status)) {
    throw new \Exception('状态值不合法');
}

```

#### 使用验证规则判断值是否合法

利用 `Yesccx\LaravelEnum\Rules\EnumRule` 验证规则来验证接口入参是否合法

``` php
use App\Enums\UserEnum;
use Yesccx\LaravelEnum\Rules\EnumRule;
use Illuminate\Support\Facades\Validator;

$validator = Validator::make(
    [
        'status' => request()->get('status')
    ],
    [
        'status' => [
            'bail',
            'required',
            'numeric',
            // 枚举验证规则，默认验证当前的字段 status
            new EnumRule(UserEnum::class)
        ]
    ]
);

// 自定义验证字段
$validator = Validator::make(
    [
        'user_status' => request()->get('status')
    ],
    [
        'user_status' => [
            'bail',
            'required',
            'numeric',
            // 枚举验证规则，手动字段验证字段 status
            new EnumRule(UserEnum::class, 'status')
        ]
    ]
);
```

#### 获取枚举值的含义说明

``` php
use App\Enums\UserEnum;

$user = User::query()->find(1);

echo $user->status; // 1
echo UserEnum::make('status')->translate($user->status); // 启用
```

#### 使用模型访问器定义字段含义说明

``` php
use App\Enums\UserEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Model
{
    /**
     * 访问器：状态-说明
     *
     * @return Attribute
     */
    public function statusDefinition(): Attribute {
        return Attribute::make(
            get: fn ($value) => UserEnum::make('status')->translate($this->status)
        )
    }
}

$user = User::query()->find(1);

echo $user->status; // 1
echo $user->status_definition; // 启用
```

#### 获取某个字段的所有可选项

可以将某个字段的有效可选项集取出，常见于前端页面中的下拉框选择等场景

``` php
use App\Enums\UserEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

// values方法会返回是一个Collection集合
$options = UserEnum::make('status')->values();

var_export($options->toArray());

// [
//     0 => '禁用',
//     1 => '启用'
// ]
```

### 进阶用法

#### 利用PHP8.1注解定义枚举类

这是定义枚举类更便捷的方式，通过引用 `Yesccx\LaravelEnum\Traits\AnnotationScan` trait 实现 `字段与字段值的映射关系` 自动发现的功能。其原理是在实例化枚举类时，会自动扫描收集常量上的注解。

``` php
<?php

namespace App\Enums;

use Yesccx\LaravelEnum\Enum;
use Yesccx\LaravelEnum\Support\Message;
use Yesccx\LaravelEnum\Traits\AnnotationScan;

final class UserEnum extends Enum
{
    use AnnotationScan;

    #[Message('status', '禁用')]
    public const STATUS__OFF = 0;

    #[Message('status', '启用')]
    public const STATUS__ON = 1;

    #[Message('gender', '男')]
    public const GENDER__MAN = 1;

    #[Message('gender', '女')]
    public const GENDER__WOMAN = 2;
}
```

#### 复用枚举值

一般业务场景中，多数表都会存在一个`status`字段(0:禁用,1:启用)，此时可以将这些能被共用的 `可枚举字段` 抽象出来，在多处复用。

这里利用了`PHP` `interface`能够定义常量、类能够多实现的特性实现了该功能，示例如下：

1. 抽象 `status` 字段的枚举值定义

```php
<?php

namespace App\Enums\Common;

use Yesccx\LaravelEnum\Support\Message;

interface CommonStatusEnum
{
    #[Message('status', '禁用')]
    public const STATUS__OFF = 0;

    #[Message('status', '启用')]
    public const STATUS__ON = 1;
}
```

2. 在不同的枚举类中复用
```php
<?php

use Yesccx\LaravelEnum\Enum;

/**
 * 用户 枚举类
 */
final UserEnum extends Enum implements CommonStatusEnum
{
    #[Message('gender', '男')]
    public const GENDER__MAN = 1;

    #[Message('gender', '女')]
    public const GENDER__WOMAN = 2;
}

/**
 * 文章 枚举类
 */
final ArticleEnum extends Enum implements CommonStatusEnum
{

}

echo UserEnum::STATUS__OFF; // 0
echo ArticleEnum::STATUS__OFF; // 0

echo UserEnum::GENDER__MAN; // 1

```

## 使用建议

- 建议将枚举类统一定义在 `App\Enums` 目录下；
- 建议一个模型类对应一个枚举类，如 `App\Models\User` 模型类对应一个 `App\Enums\UserEnum` 枚举类，然后将 `user` 表中的 `可枚举字段` 及其 `字段值` 定义到其中；
- 当可枚举字段名由多个单词组成时，字段名单词与字段值单词之间用双下划（`__`）线分割，如：`user_status` 字段对应字段值变量名 `USER_STATUS__OFF` 、`USER_STATUS__ON`，这样能有效防止 `字段名单词` 与 `字段值单词` 之间的单词边界混乱。

## TODO

- 将注解扫描到的内容做缓存（缓存到`bootstrap/cache`目录下）
- 多语言支持
- 完善文档，列出枚举类可用的方法清单

## License

MIT
