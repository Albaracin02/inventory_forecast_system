<?php
session_start();
include('includes/db.php');

$category = isset($_POST['category']) ? $_POST['category'] : ''; // Handle the scenario where no category is selected

$sql = "SELECT 
  di.item_name,
  ROUND(SUM(CASE WHEN dt.year = 2019 THEN fuc.total_cost ELSE 0 END), 2) AS total_cost_for_2019,
  ROUND(SUM(CASE WHEN dt.year = 2020 THEN fuc.total_cost ELSE 0 END), 2) AS total_cost_for_2020,
  ROUND(SUM(CASE WHEN dt.year = 2021 THEN fuc.total_cost ELSE 0 END), 2) AS total_cost_for_2021,
  ROUND(SUM(CASE WHEN dt.year = 2022 THEN fuc.total_cost ELSE 0 END), 2) AS total_cost_for_2022,
  ROUND(SUM(CASE WHEN dt.year = 2023 THEN fuc.total_cost ELSE 0 END), 2) AS total_cost_for_2023
  FROM 
  dim_item di
  LEFT JOIN 
  fact_unit_cost fuc ON di.item_id = fuc.item_id
  LEFT JOIN 
  dim_time dt ON fuc.t_id = dt.t_id";

// Append WHERE clause if a specific category is selected
if ($category != '') {
  $sql .= " WHERE di.item_category = '$category'";
}

$sql .= " GROUP BY 
di.item_category, di.item_name";

$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

$output = '';

if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $output .= "<tr>";
    $output .= "<td>" . $row['item_name'] . "</td>";
    $output .= "<td>" . formatCurrency($row['total_cost_for_2019']) . "</td>";
    $output .= "<td>" . formatCurrency($row['total_cost_for_2020']) . "</td>";
    $output .= "<td>" . formatCurrency($row['total_cost_for_2021']) . "</td>";
    $output .= "<td>" . formatCurrency($row['total_cost_for_2022']) . "</td>";
    $output .= "<td>" . formatCurrency($row['total_cost_for_2023']) . "</td>";
    $output .= "</tr>";
  }
} else {
  $output .= "<tr><td colspan='6'>No data available</td></tr>";
}

function formatCurrency($value) {
  if ($value == '0.00') {
    return '0';
  } else if ($value != 0) {
    return '₱' . number_format($value, 2, '.', ',');
  } else {
    return number_format($value, 2, '.', ',');
  }
}

echo $output;
?>
