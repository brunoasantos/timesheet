<?php
class User extends Model {
  function create($data, $type = 'employee') {
    $data['password'] = sha1($data['password']);
    $data['salt'] = sha1(sha1(APPLICATION_SALT).sha1($data['email']));
    $data['type'] = $type;
    $monthly_hours = Helper::monthly_hours($data['hours_per_day']);
    if (!$monthly_hours) {
      return false;
    }
    if (DB::write()->AutoExecute('user', $data, 'INSERT')) {
      $data['id'] = DB::write()->Insert_ID();
      return $data;
    }      
    
    return false;
  }
  
  function check_unique_email($email) {
    $rs = DB::read()->Execute('SELECT 1 FROM user WHERE email = ? LIMIT 1', array($email));
    return !($rs && $rs->RecordCount());
  }
  
  function check_credentials($data) {
   $rs = DB::read()->Execute('SELECT 1 FROM user WHERE email = ? AND password = ? LIMIT 1', array($data['email'], sha1($data['password'])));
   return $rs && $rs->RecordCount();
  }
  
  function check_salt($salt) {
    $rs = DB::read()->Execute('SELECT 1 FROM user WHERE salt = ? LIMIT 1', array($salt));
    return $rs && $rs->RecordCount();
  }
  
  function find_by_email($email) {
    $rs = DB::read()->Execute('SELECT * FROM user WHERE email = ? LIMIT 1', array($email));
    if ($rs && $rs->RecordCount()) {
      return $rs->FetchObject(false);
    }
    return false;
  }
  
  function find_by_salt($salt) {
    $rs = DB::read()->Execute('SELECT * FROM user WHERE salt = ? LIMIT 1', array($salt));
    if ($rs && $rs->RecordCount()) {
      return $rs->FetchObject(false);
    }
    return false;
  }
  
  function find_by_id($id) {
    $rs = DB::read()->Execute('SELECT * FROM user WHERE id = ? LIMIT 1', array($id));
    if ($rs && $rs->RecordCount()) {
      return $rs->FetchObject(false);
    }
    return false;
  }
  
  function save($data) {
    return DB::write()->AutoExecute('user', $data, 'UPDATE', 'id = '.Session::get('id'));
  }

  function get_hours_per_day($id) {
    $rs = DB::read()->Execute('SELECT hours_per_day FROM user WHERE id = ? LIMIT 1', array($id));
    if ($rs && $rs->RecordCount()) {
      return $rs->Fields('hours_per_day');
    }
    return 0;
  }

  function get_name($id) {
    $rs = DB::read()->Execute('SELECT name FROM user WHERE id = ? LIMIT 1', array($id));
    if ($rs && $rs->RecordCount()) {
      return $rs->Fields('name');
    }
    return 'No Name';
  }

  function get_users($type = false) {
    $users = array();

    $sql = "SELECT * FROM user";
    if ($type) {
      $sql .= " WHERE type = '{$type}'";
    }

    $rs = DB::read()->Execute($sql);
    if ($rs && $rs->RecordCount()) {
      while ($user = $rs->FetchNextObject(false)) {
        $users[] = $user;
      }
    }
    return $users;
  }
}
?>