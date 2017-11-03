# BeardPHP (a PHP mini framework)

## About me

Hi, my name is Antoine Masselot, student in Web/Mobile development HETIC (France) and working (internship) at Tradelab Programmatic Platform as a Full-Stack (bad name, better use Back-end with good notions of Front-end) developer.
I created this mini framework to test my abilities after the end of my first year of studying development and to grow my knowledges and understanding about what happens under the hood of a Back-End framework (QueryBuilder, ORM, Collections, Error Management...).
At the time I started to work on this project (~April/May 2017), I had poor notions of Dependency Injection with containers, this is the reason why I did not use this pattern here.

## What's in this repository ?

## Configuration

Everything happens in file ***config.php***, where the specs of your database connection will be asked. 
***Note*** that BeardPHP uses MySQL.

```php
// Defining Host const
define("DB_HOST","localhost"); // database host
// Defining Database Name const
define("DB_NAME", "sense"); // Database name
// Defining Database User const
define("DB_USER", "root"); // Database user
// Defining DB Password const
define("DB_PWD", "root"); // database password
```

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

### Link a Model to a Database table

***Note*** that for consistency reasons, you need to precise a singular name for Model Class and a plural name for the table.
Examples :

```php
class User extends Model {
  // BeardPHP will be looking for table 'users' when building queries for User Entity 
}

class Category extends Model {
  // BeardPHP will be looking for table 'categories'
}
```

***Note*** that if you want a custom table name not responding to BeardPHP's criterias, you can precise the table linked to Model in Model's static property `tableName`.

```php
class User extends Model {
  protected static $tableName = 'users';
}
```

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
 
***Note*** that almost every method from QueryBuilder class returns the instance of this QB in order to chain methods like below:
```php
// $qb is an instance of QueryBuilder
$qb->select(['id', 'name', 'lastname'])
->where(['lastname' => 'Masselot'])
->limit(2)
->offset(10)
->get();
```

Method `get` from QB returns a Collection object while methods `delete`, `update` and `add` return null.

***Note*** that you will mostly use it from a Model Instance, which means the table name of the Entity is already bound to the QueryBuilder Instance.

#### Instanciate a QueryBuilder Instance:
  - From Scratch :
    QueryBuilder's `constructor` method takes one parameter: the table name (string) to assign to the object.
```php
$qb = new QueryBuilder('users'); // all operations will be done on 'users' table.
```

  - From an instance of a Model:
    Use method `find` from a Model Class to instanciate a QueryBuilder and start to build a query, table used to perform actions will be the Model's tableName
```php
$qb = User::find();
// do instructions with QueryBuilder
```

#### Method `select`:
  - parameters : 
    - selected_values = (String[]) => the columns to retrieve from database, by default *
    - aliases = (String[]) => the array of aliases to assign to previously given values.
    
  - Example :
```php
// For SQL query building please use class QueryBuilder and not BeardQuery as you might experience bugs
// as BeardQuery will try to construct a Collection class and generate Model Entities from your SQL Query call
$qb = new QueryBuilder('users');
$users = $qb->select(
    ['user_id', 'name', 'lastname'], // column names
    ['userid', 'name', 'ln'] // aliases
)
->get();
```

#### Add conditions : `where`, `orWhere` and `andWhere`:

***Note*** that all three methods in this topic take an array argument under the form
```php
$condition = ['columnName' => 'value'];
$operator = '=';
$qb->where($condition, $operator);
```

***Note*** that if you want to use the 'WHERE . IN|NOT IN' clause, you can specify the condition value as an array of values.
```php
$condition = ['columnName' => ['array', 'of', 'values'];
// For a WHERE . IN clause, $operator must be either 'IN' or '=' (defaults to '=')
$operator = '=';
// For a WHERE . NOT IN clause, $operator must be either 'NOT IN' or '!='
$operatorNotIn = '!=';
```

- where(condition: array, operator: string - default '=') : QueryBuilder
  Add a 'WHERE' clause to SQL Query, take condition array (see explanation above) and the operator for condition ('=' | '!=' | 'IN' | 'NOT IN');
  ***Note*** that you will need to use methods `andWhere` or `orWhere` after you specified the first condition if you need to add other conditions.

- orWhere(condition: array, operator: string - default '=') : QueryBuilder
  Add a 'OR WHERE' clause to SQL Query, take condition array (see explanation above) and the operator for condition ('=' | '!=' | 'IN' | 'NOT IN');
  
- andWhere(condition: array, operator: string - default '=') : QueryBuilder
  Add a 'AND WHERE' clause to SQL Query, take condition array (see explanation above) and the operator for condition ('=' | '!=' | 'IN' | 'NOT IN');

```php
$qb->where(['user_id' => 2])
->orWhere(['name' => 'Antoine'], '!=')
->andWhere(['lastname' => 'Masselot']);
```

#### Limit, Offset

- limit(start: integer, end: integer - default(false)) : QueryBuilder
  Adds a 'LIMIT start' clause if end is not described, 'LIMIT start, end' if end is given. 

-offset(offset: integer) : QueryBuilder
  Adds a 'OFFSET offset' clause to SQL Query.

#### Retrieve data: `get`, `getOne`, `getAll`, `getFirst`

- get() : array
  In charge of setting up query from QueryBuilder's properties, binding values and execute SQL Query, returns the array of data retrieved from DB.

- getOne(): array
  Add a "LIMIT 1" clause to query, sets up query from QB's properties, bind values and returns an associative array of data retrieved.
   
- getAll(): array
  Reset QB's condition to be 'WHERE 1' clause, sets up query and returns array of data.
  
- getFirst(): array
  Reset QB's condition, bind values, sets up query and returns array of data.
  
#### Add

***In order*** to add a row in a table, you will have to specify the columns to affect with following method :

- addColumns(columns: String[]) : QueryBuilder

- values(values: array) : QueryBuilder

```php
$data = [
  'name'     => 'Antoine',
  'lastname' => 'Masselot'
];

// add column names to SQL Query with method addColumns
$qb->addColumns(array_keys($data));
// then add the values to bind with method values() 
$qb->values(array_values($data));
```

***At the moment***, values assigned to columns need to be at the same index in `values` array, which is a bad practice and will be updated to associative array in next patch.
Example :
```php
$qb->addColumns(['lastname', 'name'])
->values(['name to add', 'lastname to add']);
// this will cause issues as name value is not at the same index as name column.
```

- add() : null
In order to execute the query (ADD clause), use QueryBuilder's method `add`:
```php
$qb->add();
```

#### Update

***Note*** that 'updating' data works in a similar way than 'Add' described above: add columns to update with method `updateColumns` and add the values with method `values`. 

- updateColumns(columns: String[]) : QueryBuilder
  Sets up columns to update

- update() : null;
  Sets up SQL Query with a 'Update' clause, bind values and execute SQL Query.
  
```php
$data = [
  'name'     => 'Antoine',
  'lastname' => 'Flint'
];

// add column names to SQL Query with method updateColumns
$qb->updateColumns(array_keys($data));
// then add the values to bind with method values() 
$qb->values(array_values($data));
// You need to specify a condition to identify which row to update
$qb->where(['user_id' => 1])
->update();
```

***Note*** that the same issue described at the end of 'Add' topic happens here too, though it will be fixed in next patch.

#### Delete

- delete() : null
  Sets up SQL Query with 'DELETE' clause, bind values and execute Query.
  
```php
$qb = new QueryBuilder('user');
// Delete row where user_id = 1
$qb->where(['user_id' => 1])
->delete();

// Delete rows where user.lastname IN Masselot, Meow
$qb->where(['lastname' => ['Masselot', 'Meow']])
->delete();
```

#### Join tables

- leftJoin (tableName: string, joint: String[]) : QueryBuilder

- rightJoin (tableName: string, joint: String[]) : QueryBuilder

- innerJoin (tableName: string, joint: String[]) : QueryBuilder

```php
$qb = new QueryBuilder('users');

$qb->innerJoin('orders', ['users.user_id' => 'orders.user_id']);
$qb->leftJoin('addresses', ['users.address_id' => 'address.id']);
$qb->rightJoin('orders', ['users.user_id' => 'orders.user_id']);
```
 
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
    "home": {
        "path": "/",
        "controller": "Home",
        "method": "GET",
        "action": "Hello"
    }
}
```

- "home" (arbitrary): (string) name of the route
- "path": (string) URL path for the action
- "controller": (string) Controller name, ***note*** that framework will suffix controller name with 'Controller', here => 'HomeController'
- "action": (string) ***note*** that the framework will suffix action name with 'Action', here => 'HelloAction'

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
// auth/login being the path from "views" directory -> auth/login for file login.html.twig in folder /views/auth
$this->render('auth/login', ['username' => 'toto']);
```
