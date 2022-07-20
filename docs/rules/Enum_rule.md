# Enum

- `enum()`
- `Enum_rule(\Enum $enumType)`

Validates whether the input is a valid php enum or php backed enum. **This requires php 8.1 as enums is introduced in php 8.1**

```php
use AJD_validation\AJD_validation as v;

$v = new v;

enum Status : string
{
    case DRAFT = 's';
    case PUBLISHED = 'b';
    case ARCHIVED = 'a';
}

enum Sstatus
{
    case DRAFT;
    case PUBLISHED;
    case ARCHIVED;
}

$v->enum(Sstatus::class)->check('enum_field', 'a'); // will output error
$v->getValidator()->enum(Sstatus::class)->validate('a'); // false

$v->enum(Status::class)->check('enum_field', 'x'); // will output error
$v->getValidator()->enum(Status::class)->validate('x'); // false

$v->enum(Sstatus::class)->check('enum_field', Sstatus::DRAFT); // validation passes
$v->getValidator()->enum(Sstatus::class)->validate(Sstatus::DRAFT); // true

$v->enum(Status::class)->check('enum_field', 's'); // validation passes
$v->getValidator()->enum(Status::class)->validate('s'); // true

$v->enum(Status::class)->check('enum_field', Status::PUBLISHED); // validation passes
$v->getValidator()->enum(Status::class)->validate(Status::PUBLISHED); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
