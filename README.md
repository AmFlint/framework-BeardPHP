# BeardPHP (a PHP mini framework)

## About me

Hi, my name is Antoine Masselot, student in Web/Mobile development HETIC (France) and working (internship) at Tradelab Programmatic Platform as a Full-Stack (bad name, better use Back-end with good notions of Front-end) developer.
I created this mini framework to test my abilities after the end of my first year of studying development and to grow my knowledges and understanding about what happens under the hood of a Back-End framework (QueryBuilder, ORM, Collections, Error Management...).
At the time I started to work on this project (~April/May 2017), I had poor notions of Dependency Injection with containers, this is the reason why I did not use this pattern here.

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
 
##### Persist Data

In order to persist data in database from a Model's properties, I offer you a global method to do it easily : `save`
```php
// method save will either create a new row in table, or update already existing row if $user has a set primary key property
$user->save();
```
***Important*** Note that `save` method returns a boolean, which allows developers to describe different behaviors in case data is or isn't persisted.

See next topics `Create ` and `Update` to get more details about method `save`.

###### Create

In order to persist data in database from a Model's properties, I offer you two ways to do it :
***Note*** that the properties will only be assigned to your Model (when hydrating in Model's `create` method or at Object Instanciation) if you declared it properly in class Declaration (see Class Declaration example above). Non declared attributes will just be ignored.

`create` method :
```php
// the data to pass to our User's create method
$data = [
  'name' => 'Antoine',
  'lastname' => 'Masselot',
  'age' => 21
];

// create method returns an instance of the Model called, hydrated with given attributes
$user = User::create($data);
```

`save` method :
 ```php
 // the data to pass to our User's create method
 $data = [
   'name' => 'Antoine',
   'lastname' => 'Masselot',
   'age' => 21
 ];
 
 $user = new User($data); // creating instance of Object and hydrating it with data
 $user->save(); // persist data to DB
 ```
 
###### Update

In order to persist data in database from a Model's properties, I offer you two ways to do it :
  
`update` method :
```php
// For this example, $user is an instance of User with name = Antoine and lastname = Masselot
$user->update(['lastname' => 'Flint']); // row is updated in database
```
Update method returns a boolean so you can decide different behaviors whether entity is persisted or not.
  
`save` method :
  ```php
  // For this example, $user is an instance of User with name = Antoine and lastname = Masselot
  $user->lastname = 'Flint';
  $user->save();
  ```
  
##### Delete Data
```php
// For this example, $user is an instance of User, persisted in DataBase
$user->delete(); // returns boolean 
```

***
 
#### QueryBuilder
 
#### Collection
