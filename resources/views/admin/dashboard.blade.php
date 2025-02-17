<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>

<body>
    <h2>Welcome to Admin Dashboard</h2>
    <button onclick="logout()">Logout</button>

    <script>
        function logout() {
            let token = localStorage.getItem('admin_token');

            axios.post('/api/admin/logout', {}, {
                headers: { 'Authorization': `Bearer ${token}` }
            }).then(response => {
                localStorage.removeItem('admin_token');
                window.location.href = '/admin/login';
            }).catch(error => {
                console.error("Logout failed", error);
            });
        }
    </script>
</body>
</html>