<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submit Complaint</title>
</head>
<body>
  <h2>Submit a Complaint</h2>
  <form action="process_complaint.php" method="POST">
    <label for="title">Title:</label><br>
    <input type="text" id="title" name="title" required><br><br>

    <label for="description">Description:</label><br>
    <textarea id="description" name="description" rows="5" cols="50" required></textarea><br><br>

    <button type="submit">Submit Complaint</button>
  </form>
</body>
</html>