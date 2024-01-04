<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] != true) {
  header("Location: pages-login.php");
  exit;
}

$sql2 = "SELECT 
c.cat_name,
i.item_name,
ROUND(COALESCE(MAX(CASE WHEN d.year = 2019 THEN f.unit_cost ELSE 0 END), 0), 2) AS highest_cost_2019,
ROUND(COALESCE(MAX(CASE WHEN d.year = 2020 THEN f.unit_cost ELSE 0 END), 0), 2) AS highest_cost_2020,
ROUND(COALESCE(MAX(CASE WHEN d.year = 2021 THEN f.unit_cost ELSE 0 END), 0), 2) AS highest_cost_2021,
ROUND(COALESCE(MAX(CASE WHEN d.year = 2022 THEN f.unit_cost ELSE 0 END), 0), 2) AS highest_cost_2022,
ROUND(COALESCE(MAX(CASE WHEN d.year = 2023 THEN f.unit_cost ELSE 0 END), 0), 2) AS highest_cost_2023
FROM 
dim_time d
JOIN 
fact_unit_cost f ON d.t_id = f.t_id
JOIN 
dim_item i ON f.item_id = i.item_id
JOIN 
dim_category c ON f.cat_id = c.cat_id
WHERE 
d.year BETWEEN 2019 AND 2023
GROUP BY 
c.cat_name, i.item_name" ;
$result2 = mysqli_query($conn, $sql2) or die(mysqli_error($conn));




?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Supplies and Materials</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/slsu-logo.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Updated: Mar 09 2023 with Bootstrap v5.2.3
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

  <!-- ======= Header ======= -->
  <?php include("includes/header.php"); ?>
  <!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

<li class="nav-item">
  <a class="nav-link collapsed" href="dashboard.php">
    <i class="bi bi-grid"></i>
    <span>Dashboard</span>
  </a>
</li>         

<li class="nav-heading">Pages</li>

<li class="nav-item">
  <a class="nav-link collapsed" href="items.php">
    <i class="bi bi-table"></i>
    <span>Supplies and Materials</span>
  </a>
</li>

<li class="nav-item">
  <a class="nav-link active" href="forecasting.php">
    <i class="bi bi-graph-up"></i>
    <span>Forecasting</span>
  </a>
</li>

</ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Forecasting</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <h5 class="card-title" style="text-align: left;">Forecast of CSE for 2024</h5>

    <section class="section profile">
    <div class="row">
    <div class="col-xl-12">
      <div class="card">
        <div class="card-body pt-3">
          <!-- Bordered Tabs -->

          <label for="forecastDropdown" class="form-label">Select Category:</label>
<div class="d-inline-block"> <!-- Create a wrapper div -->
  <select class="form-select form-select-sm" id="forecastDropdown" style="max-width: 350px;"> <!-- Set a maximum width -->
    <option value="">All Categories</option>
    <?php
    // Retrieve unique categories from the database
    $sqlCategories = "SELECT DISTINCT item_category FROM dim_item";
    $resultCategories = mysqli_query($conn, $sqlCategories) or die(mysqli_error($conn));
    while ($category = mysqli_fetch_assoc($resultCategories)) {
      echo "<option value='" . $category['item_category'] . "'>" . $category['item_category'] . "</option>";
    }
    ?>
  </select>
</div>

              
      <div class="table-container">
      <table id="secondTabTable" class="table">
      <!-- Table headers -->
      <thead>
        <tr>
          <th style='width: 350px;'>Item Name</th>
          <th>Quantity (Avg)</th>
          <th>Quantity (LR)</th>
          <th style='width: 200px; text-align: right;'>Unit Cost for 2024</th>
          <th style='width: 200px; text-align: right;'>Total Cost for 2024</th>
        </tr>
      </thead>
      <tbody id="secondTabTableBody">
            <?php
            // Previous PHP code to fetch and display table data
            ?>
          </tbody>
    </table>
  </div>
       
            </div>
          </div><!-- End Bordered Tabs -->
        </div>
      </div>
    </div>
  </div>
  

</section>




  </main><!-- End #main -->

 

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
  $(document).ready(function() {
    // Function to load all categories data initially
    function loadAllCategoriesData() {
      $.ajax({
        url: 'items_per_category_forecast.php',
        method: 'POST',
        data: {
          category: ''
        },
        success: function(data) {
          $('#secondTabTableBody').html(data);
        }
      });
    }

    // Load all categories data on page load
    loadAllCategoriesData();

    $('#forecastDropdown').change(function() {
      var category = $(this).val();
      $.ajax({
        url: 'items_per_category_forecast.php',
        method: 'POST',
        data: {
          category: category
        },
        success: function(data) {
          $('#secondTabTableBody').html(data);
        }
      });
    });
  });
</script>


</body>

</html>