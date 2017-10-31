# BeardPHP (a PHP mini framework)

## What's in this repository ?
### Basic ORM :
BeardPHP comes with a built-in ORM going from simple Model Entities Read methods to richer relationships between entities (it even allows you to specify pivot tables), allowing developpers to customise their database structure and don't fall into a framework specific structure.

In order for the BeardORM to work properly, you will need to specify the Entity's Primary (default to `id`), used for many operations (find/findOne, relationships ...).
 
#### Example for Model Declaration 
```php
// Example : User Entity declaration with primary key 'user_id'
class User extends Model {
    // Need to specify primary key (default to 'id')
    public static $primaryKey = 'user_id';
    
    // declare the DB Schema's attributes
    public $username;
    public $password;
}
```

#### ORM Operations
##### Retrieve Model data from Database :
via method find() : 
```php
// below instructions return an instance of class Collection holding an instance of User Class.
$users = User::find(['user_id' => 1])->get(); // clearly specifying parameter name and value
$users = User::find(1)->get(); // calling User's property primaryKey (defined in previous example)
// To add a 'WHERE . IN' clause through find method, 
// you must specify an array of values like below :
$users = User::find(['user_id' => [1, 2, 3]])->get();
// Note that if you want to specify another condition for the SQL Query, use QueryBuilder's method andWhere()
$users = User::find(['name' =>['Antoine', 'Minou']])->andWhere(['lastname' => 'Masselot'], '!=')->get();
// See QueryBuilder explanations below
```
**Important** : You must know that `find` method returns an instance of the QueryBuilder, in order to retrieve data, you will have to call its method `get` (can chain like above example) which returns a Collection (see details on Collection below) of Model Objects.

To retrieve a single Entity from database, use method `findOne` :
```php
// both instructions return the same result
$user = User::findOne(['user_id' => 1]);
$user = User::findOne(1);
// $user is an instance of User Class. 
```

To retrieve all Entities responding to given condition, use method `findAll` :
 ```php
 // $users contains instance of Collection filled with User Objects retrieved
 $users = User::findAll(['lastname' => 'Masselot']);
 // to specify other conditions, use method andWhere or orWhere (example above)
 ```
 
 #### QueryBuilder
 
#### Collection
