<?php
// NOTES
//
// - Went over the allotted time.
// - Obvs the classes would all be in separate files, but I'm not sure if you wanted the clean-me returned as one file or not.
// - Sanitization has been left out. It should be handled after the POST/GET request before being sent to the query methods.
// - The query builder is absolute basic and does not account for more complex queries. Only done SELECT query because of time constraints. Should not really be used in an actual application.
// - Only read methods have been done.
//
// -----------------------------------------------------------------------------

class Database
{
    private $db_host = '127.0.0.1'; // string
    private $db_user = 'root'; // string
    private $db_pass = ''; // string
    private $db_name = 'test'; // string
    //  private $db_port = 8889; // int
    public $db_conn = null; // mysqli

    public function __construct()
    {
        try {
            $this->db_conn = new mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
        }
        catch (mysqli_sql_exception $e) {
            die($e->errorMessage());
        }
    }

    public function create($table, $fv)
    {
        $sql   = QueryBuilder::insert($table, $fv);
        $query = $this->db_conn->prepare($sql);
    }

    public function read($table, $fields, $conditions)
    {
        $sql   = QueryBuilder::where($table, $fields, $conditions);
        $query = $this->db_conn->query($sql);
        if ($query) {
            return $query->fetch_assoc();
        }
        return false;
    }

    public function update($table, $fields, $conditions)
    {
        return QueryBuilder::update($table, $fields, $conditions);
    }

    public function delete($table, $conditions)
    {
        return QueryBuilder::delete($table, $conditions);
    }


}

// -----------------------------------------------------------------------------

class QueryBuilder
{
    // Utitily class to handle query building
    public static function insert($table, $fvt)
    {
        // $fvt is associative array of fields to update,
        // value to update with and
        // type of value to bind
        $query = ''; // whatever is built
        //$query->bind_param("ssss", $this->prefix, $this->firstName, $this->lastName, $this->suffix);
    }

    public static function where($table, $fields, $conditions)
    {
        $query     = 'SELECT ';
        $table_sql = $table;

        // If fields have been provided then
        // insert them into the query.
        // Otherwise just stick a wildcard in
        if ($fields) {
            $fields_sql = implode(", ", $fields);
        } else {
            $fields_sql = '* ';
        }

        $query .= $fields_sql . 'FROM ' . $table_sql;

        if ($conditions) {
          // rel: top level relation
          // subrel: sub level relation
          // as mentioned above, this is bare bones
            $rel            = '';
            $subrel         = '';
            $conditions_sql = ' WHERE ';

            // caching last index for later checking
            // so that the relation isnt added on after the
            // final iteration
            end($conditions);
            $last = key($conditions);
            reset($conditions);

            //
            foreach ($conditions as $k => $v) {
                if ($k === 'relation') {
                    $rel = ' ' . $v . ' ';
                }
                if (is_array($v)) {
                    // caching last index again
                    end($v);
                    $sublast = key($v);
                    reset($v);

                    // If the "key" is set, then that means
                    // its a key/value/compare array and we
                    // can add it to the query
                    if (isset($v["key"])) {
                        if ($v["compare"] === "LIKE") {
                            $v["value"] = '%' . $v["value"] . '%';
                        }
                        $conditions_sql .= $v["key"] . ' ' . $v["compare"] . ' ' . $v["value"];
                        if ($k !== $last) {
                            $conditions_sql .= $rel;
                        }
                    }
                    // If this iteration is an array and "key"
                    // is not set, then this indicates
                    // that this iteration has nested conditions
                    // and will be wrapped in parenthesis
                    if (!isset($v["key"])) {
                        $conditions_sql .= '(';
                    }
                    foreach ($v as $nk => $nv) {
                        if ($nk === 'relation') {
                            $subrel = ' ' . $nv . ' ';
                        }

                        if (is_array($nv)) {
                            if (isset($nv["key"])) {
                                $conditions_sql .= $nv["key"] . ' ' . $nv["compare"] . ' ' . $nv["value"];
                                if ($nk !== $sublast) {
                                    $conditions_sql .= $subrel;
                                }
                            }
                        }
                    }
                    // closing parenthesis for nested conditions
                    if (!isset($v["key"])) {
                        $conditions_sql .= ')' . $rel;
                    }

                }

            }
            $query .= $conditions_sql;
        }
        return $query;
    }

    public static function update($table, $fields, $conditions)
    {

    }

    public static function delete($table, $conditions)
    {

    }
}

// -----------------------------------------------------------------------------

class User
{
    private $db; // mysqli
    private $table = 'users';

    public function __construct()
    {
        $this->db = new DataBase();
    }
    public function fetch($id)
    {
        $conditions = array(
            array(
                'key' => 'id',
                'value' => $id,
                'compare' => '='
            )
        );
        return $this->db->read($this->table, false, $conditions);
    }
    public function create($firstName, $secondName, $prefix = '', $suffix = '')
    {
        $fv = array(
            'prefix' => $prefix,
            'first_name' => $firstName,
            'second_name' => $secondName,
            'suffix' => $suffix
        );

        $this->db->create($this->table, $fv);
    }
    public function read($conditions = false)
    {
        return $this->db->read($this->table, false, $conditions);
    }
    public function update()
    {

    }
    public function delete()
    {

    }

}

// -----------------------------------------------------------------------------

class Property
{
    // property db info not provided so query methods are just my best guess

    private $db; // mysqli
    private $table = 'properties'; // string
    public $name; // string
    public $sleeps; // int
    public $location; // string
    public $smokingAllowed; // boolean
    public $petsAllowed; // boolean

    public function __construct()
    {
        $this->db = new DataBase();
    }
    public function fetch($id)
    {
        $conditions = array(
            array(
                'key' => 'id',
                'value' => $id,
                'compare' => '='
            )
        );
        return $this->db->read($this->table, false, $conditions);
    }
    public function create()
    {
        // intentionally blank
    }
    public function read($conditions)
    {
        return $this->db->read($this->table, false, $conditions);
    }
    public function update()
    {
        // intentionally blank
    }
    public function delete()
    {
        // intentionally blank
    }
}

// -----------------------------------------------------------------------------

$user = new User();
$user->create("Peter", "Johnson");
$newuser = $user->fetch(1);
echo $newuser["first_name"] . $newuser["second_name"];

$users = $user->read();
if ($users) {
    $out = '<table>';
    $out .= '<tr>';
    $out .= '<th>First Name</th>';
    $out .= '<th>Last Name</th>';
    $out .= '</tr>';
    foreach ($users as $user) {
        $out .= '<tr>';
        $out .= '<td>' . $user['first_name'] . '</ td>';
        $out .= '<td>' . $user['second_name'] . '</ td>';
        $out .= '</tr>';
    }
    $out .= '</table>';

    echo $out;
}

// -----------------------------------------------------------------------------

// This would normally be done as a POST request via a form with filters but I will use dummy entries as example

$property = new Property();

$searchArgs = array(
    'relation' => 'AND',
    array(
        'key' => 'name',
        'value' => 'Craster Reach',
        'compare' => 'LIKE'
    ),
    array(
        'key' => 'sleeps',
        'value' => 1,
        'compare' => '='
    ),
    array(
        'key' => 'location',
        'value' => 'Craster',
        'compare' => '='
    ),
    array(
        'key' => 'smoking',
        'value' => false,
        'compare' => '='
    ),
    array(
        'key' => 'pets',
        'value' => true,
        'compare' => '='
    )
);
$properties = $property->read($searchArgs);
if ($properties) {
    $out = '<table>';
    $out .= '<tr>';
    $out .= '<th>Property Name</th>';
    $out .= '<th>Sleeps</th>';
    $out .= '<th>Location</th>';
    $out .= '<th>Smoking Allowed</th>';
    $out .= '<th>Pets Allowed</th>';
    $out .= '</tr>';
    foreach ($properties as $property) {
        $out .= '<tr>';
        $out .= '<td>' . $property['name'] . '</ td>';
        $out .= '<td>' . $property['sleeps'] . '</ td>';
        $out .= '<td>' . $property['location'] . '</ td>';
        $out .= '<td>' . ($property['smoking']) ? 'Smoking' : 'No Smoking' . '</ td>';
        $out .= '<td>' . ($property['pets']) ? 'Pets Allowed' : 'No Pets' . '</ td>';
        $out .= '</tr>';
    }
    $out .= '</table>';

    echo $out;
}

// -----------------------------------------------------------------------------

//  Example conditions array for QueryBuilder::where()

$conditions = array(
    'relation' => 'AND',
    array(
        'key' => 'first_name',
        'value' => 'Will',
        'compare' => 'LIKE'
    ),
    array(
        'relation' => 'OR',
        array(
            'key' => 'prefix',
            'value' => 'MRS',
            'compare' => '='
        ),
        array(
            'key' => 'prefix',
            'value' => 'MR',
            'compare' => '='
        )
    ),
    array(
        'key' => 'suffix',
        'value' => 'MBE',
        'compare' => '='
    )
);

echo QueryBuilder::where('users', false, $conditions);
// returns: SELECT * FROM users WHERE first_name LIKE %Will% AND (prefix = MRS OR prefix = MR) AND suffix = MBE
