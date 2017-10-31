# BeardPHP (a PHP mini framework)

## About me

Hi, my name is Antoine Masselot, student in Web/Mobile development HETIC (France) and working (internship) at Tradelab Programmatic Platform as a Full-Stack (bad name, better use Back-end with good notions of Front-end) developer.
I created this mini framework to test my abilities after the end of my first year of studying development and to grow my knowledges and understanding about what happens under the hood of a Back-End framework (QueryBuilder, ORM, Collections, Error Management...).
At the time I started to work on this project (~April/May 2017), I had poor notions of Dependency Injection with containers, this is the reason why I did not use this pattern here.

## What's in this repository ?
## Basic ORM :
BeardPHP comes with a built-in ORM going from simple Model Entities Read methods to richer relationships between entities (it even allows you to specify pivot tables), allowing developpers to customise their database structure and don't fall into a framework specific structure.

In order for the BeardORM to work properly, you will need to specify the Entity's Primary (default to `id`), used for many operations (find/findOne, relationships ...).
 
### Example for Model Declaration 
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

## ORM Operations
### Retrieve Model data from Database :
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
 
### Persist Data

In order to persist data in database from a Model's properties, I offer you a global method to do it easily : `save`
```php
// method save will either create a new row in table, or update already existing row if $user has a set primary key property
$user->save();
```
***Important*** Note that `save` method returns a boolean, which allows developers to describe different behaviors in case data is or isn't persisted.

See next topics `Create ` and `Update` to get more details about method `save`.

### Create

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
 
### Update

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
  
### Delete Data
```php
// For this example, $user is an instance of User, persisted in DataBase
$user->delete(); // returns boolean 
```

### RelationShips

In BeardPHP, relations are available to developers, you can create a relationship between two entities via methods `hasOne` et `hasMany` described below :

#### hasOne
```php
// In this example, the class User holds a property 'address_id' referencing to an Address Model.
class User extends Model {
  public function getAddress() {
    return $this->hasOne('Address', ['address_id' => 'id']);
  }
}
```
Using `hasOne` method grants you access to a User's property `address` and contains an instance of Address Class.

```php
// this examples uses class declaration with relationships described in above example
$address = $user->address;
// you can now use all properties and methods bound to this Address instance
$address->street;
$address->postal_code;
$address->getFullAddress();
```

#### hasMany

```php
class User extends Model {
  // In this example, the class Order holds a property 'user_id' referencing to a User Model.
  public function getOrders() {
    return $this->hasMany('Order', ['id' => 'user_id']);
  }
}
```
***Note*** that calling an entity through a `hasMany` relationship, the returned value is an instance of Collection holding instances of the related Model.

```php
// this examples uses class declaration with relationships described in above example
$orders = $user->orders;
// you can now use all properties and methods bound to these Order instances through the Collection
foreach ($orders as $order) {
  // instructions
  $order->id;
  $order->price;
}
```

***Note*** that for Many-to-many (n to n) relationships, a method 'viaTable' is available, described in next topic.

#### Specify pivot tables (method `viaTable`)

```php
class User extends Model {
  public function getPictures() {
    return $this
    ->viaTable('user_pictures', ['id' => 'user_id']) // referencing user's id to a key user_id in table user_pictures
    ->hasMany('Picture', ['picture_id' => 'id']); // key picture_id in table user_pictures references the id in Picture entity
  }
}
```

### QueryBuilder
 
### Collection

The Collection class holds the data row resulting from Database call. The row items held in Collection are formatted to objects of the corresponding Model Class before being added to the Collection Object.

Iterator and ArrayAccess interfaces are being implemented inside the Collection, which allow developers to loop on the Collection's items as if it was a simple array of data, still allowing them to call for its useful formatting functions.
```php
// in this example, $orders is a Collection of Order objects retrieved from Database
foreach ($orders as $order) {
  // Instructions 
}

$order = current($orders); // get current item from Collection
```

Collection has also access to different treatment methods for data rows:
```php
// in this example, $orders is a Collection of Order objects retrieved from Database
$ordersArray = $orders->asArray(); // returns an array of arrays instead of array of objects

$numberOfOrders = $orders->count(); // count returns the number of objects held in the Collection
```

### Validating Inputs (Form Model)

### Routing

Application routing takes place in file core/config/routing.json

Every single route takes the following format :
```json
{
    "home": { // name of the route
        "path": "/", // path
        "controller": "Home", // note that framework will suffix controller name with 'Controller', here => 'HomeController'
        "method": "GET", // method being called for given route
        "action": "Hello" // action name, note that the framework will suffix action name with 'Action', here => 'HelloAction'
    }
}
```

Your Controller names must be suffixed by 'Controller' and action names suffixed by 'Action'.

***Note*** that you can also pass/expect parameters in route path and retrieve them in your Controller Actions like following :
- In route:
```json
{
    "home": {
        "path": "/users/:user_id/orders/:order_id", 
        "controller": "Order",
        "method": "GET",
        "action": "view"
    }
}
```

- In Controller:

```php
class OrderController extends Controller {
    public function viewAction($user_id, $order_id) {
        // some instructions
    }
}
```

### Rendering a view

The template view engine for BeardPHP is Twig, and the controller's way to render views is pretty similar to Twig.
Every Controllers extending the base Controller has access to a method `render` accepting two parameters: 
  - (string) path to view from views directory 
  - (array) parameters to pass to the view template
  
```php
$this->render('auth/login', ['username' => 'toto']);
```
