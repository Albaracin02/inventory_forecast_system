<?php
session_start();
include('includes/db.php');

$category = isset($_POST['category']) ? $_POST['category'] : ''; // Handle the scenario where no category is selected

$sql = "SELECT 
            di.item_name,
            di.item_category,
            f.quantity,
            f.unit_cost,
            f.total_cost,
            AVG(CASE WHEN dt.year BETWEEN 2019 AND 2023 THEN fuc.quantity ELSE 0 END) AS average_quantity_2019_to_2023
            FROM 
            dim_item di
            JOIN 
            fact_unit_cost fuc ON di.item_id = fuc.item_id
            JOIN 
            dim_time dt ON fuc.t_id = dt.t_id
            JOIN
            fact_forecast f ON fuc.item_id = f.item_id";

// Append WHERE clause if a specific category is selected
if (!empty($category)) {
    $sql .= " AND di.item_category = '$category'";
}

$sql .= " GROUP BY 
            di.item_category, di.item_name";

$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

$output = '';

if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $output .= "<tr>";
    $output .= "<td>" . $row['item_name'] . "</td>";
    $output .= "<td>" . formatQuantity($row['average_quantity_2019_to_2023']) . "</td>";
    $output .= "<td>" . formatQuantity($row['quantity']) . "</td>";
    $output .= "<td style='text-align: right;'>" . formatCurrency($row['unit_cost']) . "</td>";
    $output .= "<td style='text-align: right;'>" . formatCurrency($row['total_cost']) . "</td>";
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

function formatQuantity($value) {
    if ($value == '0') {
        return '0';
    } else {
        return number_format($value, 2, '.', ',');
    }
}

echo $output;
?>
