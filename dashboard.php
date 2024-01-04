<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] != true) {
  header("Location: pages-login.php");
  exit;
}

// Query For Total Cost Total Cost of Common-use Supplies and Equipment per year
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query
$sql = "SELECT d.year, ROUND(SUM(f.total_cost),2) AS total_cost
        FROM dim_time d
        JOIN fact_unit_cost f ON d.t_id = f.t_id
        WHERE d.year BETWEEN 2019 AND 2023
        GROUP BY d.year";

$result = $conn->query($sql);

// Fetch data and store in an array
$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$data_json = json_encode($data);



// Query For Total Cost Total Cost of Common-use Supplies and Equipment per quarter
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Your SQL query for total cost per quarter
$query = "
SELECT
d.year,
CASE
    WHEN d.quarter = 1 THEN 'Q1'
    WHEN d.quarter = 2 THEN 'Q2'
    WHEN d.quarter = 3 THEN 'Q3'
    WHEN d.quarter = 4 THEN 'Q4'
    ELSE NULL
END AS quarter,
ROUND(SUM(f.total_cost), 2) AS total_cost_per_quarter
FROM
dim_time d
JOIN
fact_unit_cost f ON d.t_id = f.t_id
WHERE
d.year BETWEEN 2019 AND 2023
GROUP BY
d.year, d.quarter
ORDER BY
d.year, d.quarter;

";

$result = $conn->query($query);

// Fetch data into an associative array
$quarterdata = array();
while ($row = $result->fetch_assoc()) {
    $year = $row['year'];
    $quarter = $row['quarter'];
    $totalCost = '₱' . number_format($row['total_cost_per_quarter'], 2, '.', ','); // Add peso sign and commas

    // Storing data by year and quarter
    $quarterdata[$year][$quarter] = $totalCost;
}

$uniquequarterYears = array_keys($quarterdata);


// Query For Total Cost Total Cost of Common-use Supplies and Equipment per category
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    // Fetch data from the database
    $query = "SELECT d.year, i.item_category, ROUND(SUM(f.total_cost),2) AS total_cost
    FROM dim_time d
    JOIN fact_unit_cost f ON d.t_id = f.t_id
    JOIN dim_item i ON f.item_id = i.item_id
    WHERE d.year BETWEEN 2019 AND 2023
    GROUP BY d.year, i.item_category
    ORDER BY d.year, i.item_category;"; // Replace 'value_column' and 'your_table' with your column and table names
    
    $result = $conn->query($query);

    $categorydata = array();
    while ($row = $result->fetch_assoc()) {
        $year = $row['year'];
        $category = $row['item_category'];
        $total_cost_category = '₱' . number_format($row['total_cost'], 2, '.', ','); // Add peso sign and commas
    
        // Storing data by year and quarter
        $categorydata[$year][$category] = $total_cost_category;
    }
    
    $uniquecategoryYears = array_keys($categorydata);


// Query For Total Cost Total Cost of Common-use Supplies and Equipment per category for every year
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to retrieve data from the database
$sql = "SELECT d.year, i.item_category, ROUND(SUM(f.total_cost),2) AS total_cost
FROM dim_time d
JOIN fact_unit_cost f ON d.t_id = f.t_id
JOIN dim_item i ON f.item_id = i.item_id
WHERE d.year BETWEEN 2019 AND 2023
GROUP BY d.year, i.item_category
ORDER BY d.year, i.item_category;

        ";
$result = $conn->query($sql);

$data = array();
$categories = array();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
      $year = $row['year'];
      $category = $row['item_category'];
      $cost = (float)$row['total_cost'];

      if (!in_array($category, $categories)) {
          $categories[] = $category;
      }

      $data[$year][$category] = $cost;
  }
}

// Generating the series data for ApexCharts
$years = range(2019, 2023);
$seriesData = array();

foreach ($categories as $category) {
  $categoryData = array();
  foreach ($years as $year) {
      $categoryData[] = isset($data[$year][$category]) ? $data[$year][$category] : 0;
  }
  $seriesData[] = array(
      'name' => $category,
      'data' => $categoryData
  );
}


// Query For Total Cost Total Cost of Common-use Supplies and Equipment per month
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Your SQL query
$query = "SELECT d.year, d.month, (SUM(f.total_cost)) AS total_cost_per_month
          FROM dim_time d
          JOIN fact_unit_cost f ON d.t_id = f.t_id
          WHERE d.year BETWEEN 2019 AND 2023
          GROUP BY d.year, d.month
          ORDER BY d.year, FIELD(d.month,
              'January', 'February', 'March', 'April', 'May', 'June',
              'July', 'August', 'September', 'October', 'November', 'December')";

$result = $conn->query($query);

// Fetch data into an associative array
$data = array();
while ($row = $result->fetch_assoc()) {
    $year = $row['year'];
    $month = $row['month'];
    $totalCost = '₱' . number_format($row['total_cost_per_month'], 2, '.', ','); // Add peso sign and commas

    // Storing data by year and month
    $data[$year][$month] = $totalCost;
}

// Get unique years for the dropdown
$uniqueYears = array_keys($data);


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard</title>
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
        <a class="nav-link active" href="dashboard.php">
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
        <a class="nav-link collapsed" href="buttonforecast.php">
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
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
    <div class="row">
    <div class="col-lg-6">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Total Cost of Common-use Supplies and Equipment per year</h5>

            <!-- Bar Chart -->
            <div id="barChart" style="height: 425px;"></div>

            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    var data = <?php echo $data_json; ?>;
                    var years = data.map(function(item) {
                        return item.year;
                    });
                    var costs = data.map(function(item) {
                        return item.total_cost;
                    });

                    // Format costs with peso sign and commas
                    var formattedCosts = costs.map(function(cost) {
                        return '₱' + cost.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    });

                    new ApexCharts(document.querySelector("#barChart"), {
                        series: [{
                            data: costs
                        }],
                        chart: {
                            type: 'bar',
                            height: 400
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                vertical: true,
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        xaxis: {
                            categories: years
                        },
                        yaxis: {
                            labels: {
                                formatter: function(value) {
                                    return '₱' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                }
                            }
                        }
                    }).render();
                });
            </script>
            <!-- End Bar Chart -->
        </div>
    </div>
</div>


<div class="col-lg-6">
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Total Cost of Common-use Supplies and Equipment per quarter</h5>

      <!-- Dropdown for Year Selection -->
      <label for="yearDropdown">Select year:</label>
      <select id="yearDropdown">
        <?php
          // Assuming $uniqueYears contains the unique years fetched from PHP
          foreach ($uniquequarterYears as $year) {
            echo "<option value='$year'>$year</option>";
          }
        ?>
      </select>

      <!-- Pie Chart -->
      <div id="seconddonutChart" style="min-height: 400px;" class="echart"></div>

      <script>
  document.addEventListener("DOMContentLoaded", () => {
          const initialData = <?php echo json_encode($quarterdata[array_values($uniquequarterYears)[0]]); ?>;
          
          // Initialize echarts with initial data
          const chart = echarts.init(document.querySelector("#seconddonutChart"));
          chart.setOption({
            title: {
              text: '',
              left: 'center'
            },
            tooltip: {
              trigger: 'item',
              formatter: function(paramsquarter) {
                return `${paramsquarter.seriesName}<br>${paramsquarter.name} : ₱${paramsquarter.value.toLocaleString()} (${paramsquarter.percent}%)`;
              }
            },
            series: [{
              name: 'Total Cost',
              type: 'pie',
              radius: ['50%', '70%'], // Adjust inner and outer radius to create a donut shape
              data: Object.entries(initialData).map(([category, value], index) => ({
                name: category,
                value: parseInt(value.replace('₱', '').replace(',', '')), // Parsing the cost value
                label: {
                  formatter: '{b}: ({d}%)' // Display only the category name and percentage in label
                }
              })),
              emphasis: {
                itemStyle: {
                  shadowBlur: 10,
                  shadowOffsetX: 0,
                  shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
              },
              // Define colors for each section of the donut chart
              color: ['#5470C6', '#91CC75', '#EE6666', '#F4B248', '#7262CB', '#6AAB9C', '#B0D87B', '#FF9F7F', '#E5CF0D']
            }]
          });

          // Function to update chart based on selected year
          document.getElementById('yearDropdown').addEventListener('change', function() {
            const selectedYear = this.value;
            const selectedData = <?php echo json_encode($quarterdata); ?>;
            const newData = selectedData[selectedYear];

            chart.setOption({
              series: [{
                data: Object.entries(newData).map(([category, value], index) => ({
                  name: category,
                  value: parseInt(value.replace('₱', '').replace(',', '')), // Parsing the cost value
                  label: {
                    formatter: '{b}: ({d}%)' // Display only the category name and percentage in label
                  }
                })),
              }]
            });
          });
        });
</script>

      <!-- End Pie Chart -->

    </div>
  </div>
</div>


    </div>
    </section>


    <section class="section">
      <div class="row">

      <div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Total Cost of Common-use Supplies and Equipment per month</h5>

            <!-- Dropdown menu for years -->
            <div>
                <label for="yearSelect">Select Year:</label>
                <select id="yearSelect" onchange="updateChart()">
                    <?php foreach ($uniqueYears as $year) : ?>
                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- End of Dropdown menu -->

            <!-- Bar Chart -->
            <div id="secondbarChart"></div>

            <!-- Include ApexCharts library -->
            <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>

            <script>
    let chart;

    const yearData = <?php echo json_encode($data); ?>;

    function updateChart() {
        if (chart) {
            chart.destroy(); // Destroy the previous chart instance
        }

        const selectedYear = document.getElementById("yearSelect").value;

        const monthsInYear = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        const chartData = [];
        const chartCategories = [];

        monthsInYear.forEach(month => {
            if (yearData[selectedYear][month]) {
                // Remove currency symbol and commas, then convert to float
                const cost = parseFloat(yearData[selectedYear][month].replace('₱', '').replace(/,/g, ''));
                chartData.push(cost);
                chartCategories.push(month);
            } else {
                chartData.push(null); // Push null for months with no data
                chartCategories.push(month);
            }
        });

       chart = new ApexCharts(document.querySelector("#secondbarChart"), {
            series: [{
                name: 'Total Cost',
                data: chartData
            }],
            chart: {
                type: 'bar',
                height: 350
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: false,
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: chartCategories
            },
            yaxis: {
                labels: {
                    formatter: function(valdropdown) {
                        // Format y-axis label with currency symbol and commas
                        return '₱' + valdropdown.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    }
                }
            },
            tooltip: {
                enabled: true,
                y: {
                    formatter: function(valdropdown) {
                        // Format tooltip value with currency symbol and commas
                        return '₱' + valdropdown.toLocaleString();
                    }
                }
            }
        });
        chart.render();
    }

    updateChart(); // Initial chart render for default selected year on page load
</script>
            <!-- End Bar Chart -->
        </div>
    </div>
</div>

      </div>
    </section>


<section class="section">
      <div class="row">


      <div class="col-lg-12">
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Total Cost of Common-use Supplies and Equipment per category</h5>

      <!-- Year Select Dropdown -->
      <label for="catyearDropdown">Select year</label>
      <select id="catyearDropdown">
        <?php
          // Assuming $uniquecategoryYears contains the unique years fetched from PHP
          foreach ($uniquecategoryYears as $year) {
            echo "<option value='$year'>$year</option>";
          }
        ?>
      </select>
      <!-- End Year Select Dropdown -->

      <!-- Donut Chart -->
      <div id="donutChart" style="height: 475px;" class="echart"></div>
          
      <script>
        document.addEventListener("DOMContentLoaded", () => {
          const initialData = <?php echo json_encode($categorydata[array_values($uniquecategoryYears)[0]]); ?>;
          const chart = echarts.init(document.querySelector("#donutChart"));
          chart.setOption({
            title: {
              text: '',
              left: 'center'
            },
            tooltip: {
              trigger: 'item',
              formatter: function(params) {
                const formattedValue = parseFloat(params.value.replace('₱', '').replace(/,/g, '')).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                return `${params.seriesName}<br>${params.name} : ₱${formattedValue} (${params.percent.toFixed(2)}%)`;
              }
            },
            series: [{
              name: 'Total Cost',
              type: 'pie',
              radius: ['50%', '70%'],
              data: Object.entries(initialData).map(([category, value], index) => ({
                name: category,
                value: parseFloat(value.replace('₱', '').replace(/,/g, '')).toFixed(2),
                label: {
                  formatter: '{b}: ({d}%)' // Display only the category name and percentage in inner label
                }
              })),
              emphasis: {
                itemStyle: {
                  shadowBlur: 10,
                  shadowOffsetX: 0,
                  shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
              },
              color: ['#5470C6', '#91CC75', '#EE6666', '#F4B248', '#7262CB', '#6AAB9C', '#B0D87B', '#FF9F7F', '#E5CF0D']
            }]
          });

          document.getElementById('catyearDropdown').addEventListener('change', function() {
            const selectedYear = this.value;
            const selectedData = <?php echo json_encode($categorydata); ?>;
            const newData = selectedData[selectedYear];

            chart.setOption({
              series: [{
                data: Object.entries(newData).map(([category, value], index) => ({
                  name: category,
                  value: parseFloat(value.replace('₱', '').replace(/,/g, '')).toFixed(2),
                  label: {
                    formatter: '{b}: ({d}%)' // Display only the category name and percentage in inner label
                  }
                })),
              }]
            });
          });
        });
      </script>
      <!-- End Donut Chart -->

    </div>
  </div>
</div>


      </div>
</section>


<section class="section">
<div class="row">

<div class="col-lg-12">
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Total Cost of Common-use Supplies and Equipment per category for every year</h5>

      <!-- Column Chart -->
      <div id="columnChart"></div>

      <script>
  document.addEventListener("DOMContentLoaded", () => {
    const seriesData = <?php echo json_encode($seriesData); ?>;
    const years = <?php echo json_encode($years); ?>;
    new ApexCharts(document.querySelector("#columnChart"), {
      series: seriesData,
      chart: {
        type: 'bar',
        height: 450
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '55%',
          endingShape: 'rounded'
        },
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
      },
      xaxis: {
        categories: years,
      },
      yaxis: {
        title: {},
        labels: {
          formatter: function (value) {
            return '₱' + value.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); // Adding peso sign, commas, and rounding to 2 decimal places
          }
        }
      },
      fill: {
        opacity: 1
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return "₱" + val.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); // Adding peso sign, commas, and rounding to 2 decimal places
          }
        }
      },
      colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A'] // Define custom colors for each column
    }).render();
  });
</script>
      <!-- End Column Chart -->
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

</body>

</html>