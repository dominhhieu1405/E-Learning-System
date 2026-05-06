<?php
if (isset($_POST['id']) && isset($_POST['key']) && isset($_POST['action'])){
    $additionalParams = $_POST["additionalParams"] ?? '';
    die(file_get_contents("https://my.noxproxy.com/modules/servers/proxypanel/api.php?id=".$_POST['id']."&key=".$_POST['key']."&a=" . $_POST['action'] . $additionalParams));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proxy Panel API</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <style>
    /* Add your styles here */
  </style>
</head>
<body>
  <h1>Proxy Panel API</h1>
  
  <form id="apiForm">
    <label for="id">ID:</label>
    <input type="text" id="id" name="id" required>

    <label for="key">API Key:</label>
    <input type="text" id="key" name="key" required>

    <label for="action">Choose an action:</label>
    <select id="action" name="action" required>
      <option value="info">Get Service Information</option>
      <option value="rotate">Rotate NOW</option>
      <option value="setrotate">Set Rotation Time</option>
      <option value="authip">Set/Clear Authorized IPs</option>
      <option value="proxies">Retrieve List with Proxies</option>
      <option value="password">Set New Password</option>
      <option value="reboot">Reboot Proxy Instance</option>
    </select>

    <div id="additionalParams"></div>

    <button type="button" onclick="makeApiRequest()">Make API Request</button>
  </form>

  <div id="response"></div>

  <script>
    function makeApiRequest() {
      var id = document.getElementById('id').value;
      var key = document.getElementById('key').value;
      var action = document.getElementById('action').value;

      var apiUrl = `https://my.noxproxy.com/modules/servers/proxypanel/api.php?id=${id}&key=${key}&a=${action}`;

      var additionalParams = '';
      switch (action) {
        case 'setrotate':
          var minutes = prompt('Enter minutes for rotation time (provide 0 to disable rotation):');
          additionalParams = `&minutes=${minutes}`;
          break;
        case 'authip':
          var ips = prompt('Enter IPs (comma-separated) for authorized IPs:');
          additionalParams = `&ips=${ips}`;
          break;
        case 'password':
          var newPassword = prompt('Enter new password (8 characters, alphanumeric):');
          additionalParams = `&password=${newPassword}`;
          break;

        default:
          break;
      }

      $.post(window.location.href, {id: id, key: key, action: action, additionalParams: additionalParams}, function(e){
          document.getElementById('response').innerHTML = e
      })

    }

    document.getElementById('action').addEventListener('change', function() {
      var selectedAction = this.value;
      var additionalParamsContainer = document.getElementById('additionalParams');

      additionalParamsContainer.innerHTML = '';

      switch (selectedAction) {
        case 'setrotate':
          additionalParamsContainer.innerHTML = '<label for="minutes">Minutes:</label><input type="number" id="minutes" name="minutes" required>';
          break;
        case 'authip':
          additionalParamsContainer.innerHTML = '<label for="ips">Authorized IPs (comma-separated):</label><input type="text" id="ips" name="ips" required>';
          break;
        case 'password':
          additionalParamsContainer.innerHTML = '<label for="newPassword">New Password (8 characters, alphanumeric):</label><input type="password" id="newPassword" name="newPassword" required>';
          break;
      }
    });
  </script>
</body>
</html>
