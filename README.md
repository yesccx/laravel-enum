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
- [安装](#安装)
  - [运行环境](#运行环境)
- [开始使用](#开始使用)
  - [定义枚举类](#定义枚举类)
  - [使用枚举类](#使用枚举类)
    - [利用枚举类进行值判断](#利用枚举类进行值判断)
    - [判断值是否合法](#判断值是否合法)
    - [使用验证规则判断值是否合法](#使用验证规则判断值是否合法)
    - [获取枚举值的含义说明](#获取枚举值的含义说明)
    - [使用模型访问器定义字段含义说明](#使用模型访问器定义字段含义说明)
    - [获取某个字段的枚举值范围](#获取某个字段的枚举值范围)
  - [进阶用法](#进阶用法)
    - [枚举集合类](#枚举集合类)
    - [自定义枚举类含义及映射](#自定义枚举类含义及映射)
    - [复用枚举值](#复用枚举值)
- [API](#api)
  - [配置项](#配置项)
    - [enum\_root\_path](#enum_root_path)
    - [cache\_filename](#cache_filename)
  - [枚举类方法](#枚举类方法)
    - [translate(mixed $value, mixed $default = null): mixed](#translatemixed-value-mixed-default--null-mixed)
    - [public function has(mixed $value): bool](#public-function-hasmixed-value-bool)
    - [public function values(): array](#public-function-values-array)
    - [public function useColumn(string $column): static](#public-function-usecolumnstring-column-static)
    - [public function by(string $column): static](#public-function-bystring-column-static)
  - [命令行](#命令行)
    - [enum:cache](#enumcache)
    - [enum:clear](#enumclear)
- [使用建议](#使用建议)
- [License](#license)

## 功能特点

- 支持 `Laravel 9+`
- 统一管理表或对象的可枚举字段，在任意处利用枚举类对值进行合法性校验
- 利用枚举类进行表单验证规则
- 利用预定义的映射关系，解析出枚举值对应的含义说明
- 使用 `PHP8.1` 的注解功能快速定义枚举类


## 安装

### 运行环境

| 运行环境要求           |
| ---------------------- |
| PHP ^8.1.0             |
| Laravel Framework ^9.0 |

``` shell
> composer require yesccx/laravel-enum

# 如果没有自定义配置项的需求，可以不进行初始化配置文件, 配置项内容参考末尾的API部分
> php artisan vendor:publish --tag=enum-config
```

## 开始使用

### 定义枚举类

通过继承枚举基类 `Yesccx\Enum\BaseEnum` 定义枚举类，利用类的成员常量来定义枚举值，并使用 `Yesccx\Enum\Supports\Message` 注解来说明枚举值的含义，`Message` 注解的作用是为了收集并管理枚举值，为后续的枚举类相关操作提供基础数据。

``` php
<?php

declare(strict_types = 1);

namespace App\Enums\User;

use Yesccx\Enum\BaseEnum;
use Yesccx\Enum\Supports\Message;

# 用户-启用状态
final class StatusEnum extends BaseEnum
{
    #[Message('禁用')]
    public const OFF = 0;

    #[Message('启用')]
    public const ON = 0;
}
```

> 此处 `Message` 注解只传递了一个参数，该参数代表枚举值的含义说明

### 使用枚举类

#### 利用枚举类进行值判断

``` php
<?php

declare(strict_types = 1);

use App\Enums\User\StatusEnum;

function check_user_status(User $user): User
{
    # 判断值是否符合预期
    if ($user->status != StatusEnum::ON) {
        throw new \Exception('用户已被禁用！');
    }

    return $user;
}
```

#### 判断值是否合法

实例化枚举类，通过 `has` 方法判断某个值是否在这个字段的有效值范围内。

``` php
<?php

declare(strict_types = 1);

use App\Enums\User\StatusEnum;

$status = (int) request()->get('status', 0);

# 方式1：常规方式实例化
if (!(new StatusEnum)->has($status)) {
    throw new \Exception('状态值不合法');
}

# 方式2：通过make方法便捷实例化
if (!StatusEnum::make()->has($status)) {
    throw new \Exception('状态值不合法');
}

```

#### 使用验证规则判断值是否合法

利用验证规则 `Yesccx\Enum\Rules\EnumRule` 来验证接口入参是否合法

``` php
<?php

declare(strict_types = 1);

use App\Enums\User\StatusEnum;
use Yesccx\Enum\Rules\EnumRule;
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
            new EnumRule(UserEnum::class, suffixMessage: '无效')
        ]
    ]
);
```

> 可以通过参数 `suffixMessage` 来指定验证失败时的错误后缀信息，默认情况下错误后缀信息为“不是有效的值”。

或者可以为枚举类引入 `Yesccx\Enum\Traits\ToRule` 后快捷的通过 `toRule` 方法构建验证规则：

``` php
<?php

declare(strict_types = 1);

use App\Enums\User\StatusEnum;
use Yesccx\Enum\Rules\EnumRule;
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
            UserEnum::toRule(suffixMessage: '无效')
        ]
    ]
);
```

#### 获取枚举值的含义说明

实例化枚举类，获取某个枚举值的含义说明

``` php
<?php

declare(strict_types = 1);

use App\Enums\User\StatusEnum;

$user = User::query()->find(1);

echo $user->status; # 1
echo StatusEnum::make()->translate($user->status); # 启用
```

#### 使用模型访问器定义字段含义说明

``` php
<?php

use App\Enums\User\StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Model
{
    /**
     * 访问器：状态-说明
     *
     * @return Attribute
     */
    public function statusDefinition(): Attribute
    {
        return Attribute::make(
            get: fn () => StatusEnum::make()->translate($this->status)
        )
    }
}

$user = User::query()->find(1);

echo $user->status; # 1
echo $user->status_definition; # 启用
```

或者可以为枚举类引入 `Yesccx\Enum\Traits\ToModelAttribute` 后快捷的通过 `makeAttribute` 或 `toAttribute` 方法构建 `Casts Attribute`：

``` php
<?php

use App\Enums\User\StatusEnum;
use App\Enums\User\GenderEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Model
{
    /**
     * 访问器：状态-说明
     *
     * @return Attribute
     */
    public function statusDefinition(): Attribute
    {
        return StatusEnum::make()->toAttribute($this, 'status', default: '');
    }

    /**
     * 访问器：性别-说明
     *
     * @return Attribute
     */
    public function genderDefinition(): Attribute
    {
        return GenderEnum::makeAttribute($this, default: '');
    }
}
```

通常情况下可以直接使用静态方法 `makeAttribute` ，他会根据上下文解析出字段名，如上述模型中的 `genderDefinition` 方法调用 `makeAttribute`时，会根据 `genderDefinition` 解析出字段名为 `gender`，最终的效果相当于
`GenderEnum::make('gender')->toAttribute($this, 'gender', '')`

#### 获取某个字段的枚举值范围

可以将某个字段的有效可选项集取出，常见于为前端页面中的下拉框选择提供数据源等场景。

``` php
<?php

declare(strict_types = 1);

use App\Enums\User\StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

$options = StatusEnum::make()->values();

var_export($options);

# [
#     0 => '禁用',
#     1 => '启用'
# ]
```

### 进阶用法

#### 枚举集合类

普通枚举类中声明的是某一类型或某一字段的枚举值范围，枚举集合可以将多个字段的枚举值都声明在其中。以数据表举例，一个 `Users` 表通常包含可枚举字段 `status` 、`gender`，此时我们可以定义一个枚举集合类 `UserEnum` 来存放对应表中的所有枚举字段的取值，示例如下：

``` php
<?php

declare(strict_types = 1);

namespace App\Enums\Model;

use Yesccx\Enum\BaseENum;
use Yesccx\Enum\Contracts\EnumCollection;

final class UserEnum extends BaseEnum implements EnumCollection
{
    /* ----------- 启用状态 ------------- */

    #[Message('status', '禁用')]
    public const STATUS__OFF = 0;

    #[Message('status', '启用')]
    public const STATUS__ON = 0;

    /* ----------- 性别 ------------- */

    #[Message('gender', '未知')]
    public const GENDER__UNKNOWN= 0;

    #[Message('gender', '男')]
    public const GENDER__MAN = 1;

    #[Message('gender', '女')]
    public const GENDER__WOMAN = 2;
}
```

上述的枚举类通过实现接口 `EnumCollection` (PS：实际上不需要额外实现任何方法)来标识该枚举类是一个枚举集合类，此时 `Message` 注解将需要传递两个参数，第一个参数为枚举值所属类型/字段，第二个参数为含义说明。

对于枚举集合类而言，在相应的使用写法上也在有所区别：

``` php
<?php

echo UserEnum::STATUS__ON; # 1

# 判断值是否合法
echo UserEnum::make('status')->has(1); # true
echo UserEnum::make('gender')->has(1); # true

# 获取值的含义说明
echo UserEnum::make('status')->translate(1); # 启用
echo UserEnum::make('gender')->translate(1); # 男

# 获取取值范围
echo UserEnum::make('status')->values(); # [ 0 => '禁用', 1 => '启用' ]
echo UserEnum::make('gender')->values(); # [ 0 => '未知', 1 => '男' , 2 => '女' ]

# 表单验证规则
$validator = Validator::make(
    [
        'status' => request()->get('status')
    ],
    [
        'status' => [
            'bail',
            'required',
            'numeric',
            new EnumRule(UserEnum::class, 'status', suffixMessage: '无效')
            // 当枚举类引入ToRule时，还可以这么写
            UserEnum::toRule('status', suffixMessage: '无效')
        ]
    ]
);

```

#### 自定义枚举类含义及映射

默认情况下会通过 `Message` 注解收集枚举类上的枚举值含义说明及值映射关系，某些特殊场景下 `Message` 注解可能无法满足我们的需求，此时可以通过重写 `BaseEnum` 中的 `loadColumnMap` 方法来自定义映射关系。

``` php
<?php

declare(strict_types = 1);

namespace App\Enums\User;

use Yesccx\Enum\BaseEnum;
use Yesccx\Enum\Supports\Message;

# 用户-启用状态
final class StatusEnum extends BaseEnum
{
    public const OFF = 0;

    public const ON = 0;

    public function loadColumnMap(): array
    {
        # Anything ...

        return [
            self::OFF => '禁用',
            self::OFF => '启用'
        ]
    }
}
```

> PS:
>
> `Message` 注解收集的数据，可以选择性的进行缓存(通过config配置)，这样能提高一定的性能。
>
> 仅在实例化自定义的枚举类时，才会执行 `loadColumnMap` 方法进行信息收集。

#### 复用枚举值

一般业务场景中，多数表都会存在一个`status`字段(0:禁用,1:启用)，此时可以将这些能被共用的 `可枚举字段` 抽象出来，在多处复用。

1. 抽象 `status` 字段的枚举值定义

``` php
<?php

namespace App\Enums\Common;

use Yesccx\Enum\Supports\Message;

interface CommonStatusEnum
{
    #[Message('status', '禁用')]
    public const STATUS__OFF = 0;

    #[Message('status', '启用')]
    public const STATUS__ON = 1;
}
```

2. 在不同的枚举类中复用
``` php
<?php

use Yesccx\Enum\BaseEnum;

# 用户状态
final class UserStatusEnum extends BaseEnum implements CommonStatusEnum
{
}

/**
 * 文章 枚举类
 */
final class ArticleStatusEnum extends BaseEnum implements CommonStatusEnum
{

}

echo UserStatusEnum::OFF; # 0
echo ArticleEnum::OFF; # 0
```

## API

### 配置项

#### enum_root_path

枚举类根目录，声明枚举类所在目录来告知注解扫描位置，默认情况下为 `app/Enums`。

#### cache_filename

枚举缓存文件，用户存放注解收集到的信息，默认为 `bootstrap/cache/enum.php`。

### 枚举类方法

#### translate(mixed $value, mixed $default = null): mixed

翻译字段值的含义，不存在含义定义时，将返回默认值

#### public function has(mixed $value): bool

判断值是否合法

#### public function values(): array

获取值集合

#### public function useColumn(string $column): static

指定字段名，可链式调用 `has`、`values`、`translate` 等方法。

#### public function by(string $column): static

指定字段名(useColumn别名)，可链式调用 `has`、`values`、`translate` 等方法。

### 命令行

#### enum:cache

扫描并构建注解枚举缓存

``` shell
php artisan enum:cache
```

> PS:
>
> 可选配置 `enum.enum_root_path` 及 `enum.cache_filename`
>
> 建议在生产环境下使用

#### enum:clear

清理注解枚举缓存

``` shell
php artisan enum:clear
```


## 使用建议

- 建议将枚举类文件统一存放在 `App\Enums` 目录中，再在此目录下按来源类型区分目录存放，如： `App\Enums\Model\...` 、 `App\Enums\Common\...`等；
- 当定义枚举集合类时，建议用两个下划线( `__` )对字段名与变量名之间进行分隔，如：`USER_STATUS__ON`，这样能有效的防止含义边界混乱；
- 在生产环境下使用时，建议开启注解收集缓存。

## License

MIT
