# User admin

Entity user Admin: `Proximum\Vimeet\Domain\Model\Admin`

How to get a user admin from the controller:

```php
public function __invoke(AdminDomain $adminDomain)
{
    $admin = $adminDomain->getAdmin();
}
```
