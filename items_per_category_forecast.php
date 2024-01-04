<?php
session_start();
include('includes/db.php');

$category = isset($_POST['category']) ? $_POST['category'] : '';

// SQL query to retrieve data for highest costs from 2019 to 2023 for selected or all categories
$sql = "SELECT 
            c.cat_name,
            i.item_name,
            ROUND(COALESCE(MAX(CASE WHEN d.year = 2019 THEN f.unit_cost ELSE 0 END), 0), 2) AS highest_cost_2019,
            ROUND(COALESCE(MAX(CASE WHEN d.year = 2020 THEN f.unit_cost ELSE 0 END), 0), 2) AS highest_cost_2020,
            ROUND(COALESCE(MAX(CASE WHEN d.year = 2021 THEN f.unit_cost ELSE 0 END), 0), 2) AS highest_cost_2021,
            ROUND(COALESCE(MAX(CASE WHEN d.year = 2022 THEN f.unit_cost ELSE 0 END), 0), 2) AS highest_cost_2022,
            ROUND(COALESCE(MAX(CASE WHEN d.year = 2023 THEN f.unit_cost ELSE 0 END), 0), 2) AS highest_cost_2023,
            COALESCE(SUM(CASE WHEN d.year = 2019 THEN f.quantity ELSE 0 END), 0) AS total_quantity_2019,
            COALESCE(SUM(CASE WHEN d.year = 2020 THEN f.quantity ELSE 0 END), 0) AS total_quantity_2020,
            COALESCE(SUM(CASE WHEN d.year = 2021 THEN f.quantity ELSE 0 END), 0) AS total_quantity_2021,
            COALESCE(SUM(CASE WHEN d.year = 2022 THEN f.quantity ELSE 0 END), 0) AS total_quantity_2022,
            COALESCE(SUM(CASE WHEN d.year = 2023 THEN f.quantity ELSE 0 END), 0) AS total_quantity_2023,
            AVG(CASE WHEN d.year BETWEEN 2019 AND 2023 THEN f.quantity ELSE 0 END) AS average_quantity_2019_to_2023
        FROM 
            dim_time d
        JOIN 
            fact_unit_cost f ON d.t_id = f.t_id
        JOIN 
            dim_item i ON f.item_id = i.item_id
        JOIN 
            dim_category c ON f.cat_id = c.cat_id
        WHERE 
            d.year BETWEEN 2019 AND 2023";

// Append WHERE clause if a specific category is selected
if (!empty($category)) {
    $sql .= " AND c.cat_name = '$category'";
}

$sql .= " GROUP BY 
            c.cat_name, i.item_name";

$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

$output = '';

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Extracting historical costs
        $costs = [
            $row['highest_cost_2019'],
            $row['highest_cost_2020'],
            $row['highest_cost_2021'],
            $row['highest_cost_2022'],
            $row['highest_cost_2023']
        ];

        // Extracting historical quantities
        $quantities = [
            $row['total_quantity_2019'],
            $row['total_quantity_2020'],
            $row['total_quantity_2021'],
            $row['total_quantity_2022'],
            $row['total_quantity_2023']
        ];

        $average_quantity_2019_to_2023 = $row['average_quantity_2019_to_2023'];

        // Years 2019 to 2023
        $years = [1, 2, 3, 4, 5];

        // Calculating average of years and costs
        $sum_x = array_sum($years);
        $sum_y_cost = array_sum($costs);
        $sum_y_quantity = array_sum($quantities);
        $sum_xy_cost = 0;
        $sum_xy_quantity = 0;
        $sum_xx = 0;
        $n = count($years);

        foreach ($years as $key => $year) {
            $sum_xy_cost += ($year * $costs[$key]);
            $sum_xy_quantity += ($year * $quantities[$key]);
            $sum_xx += ($year * $year);
        }

        // Calculate slope (b) and intercept (a) for linear regression of cost
        $slope_cost = ($n * $sum_xy_cost - $sum_x * $sum_y_cost) / ($n * $sum_xx - $sum_x * $sum_x);
        $intercept_cost = ($sum_y_cost - $slope_cost * $sum_x) / $n;

        // Calculate forecasted cost for 2024
        $forecast_cost_2024 = $intercept_cost + $slope_cost * 6; // Assuming 2024 corresponds to 6 in the converted scale

        // Calculate slope (b) and intercept (a) for linear regression of quantity
        $slope_quantity = ($n * $sum_xy_quantity - $sum_x * $sum_y_quantity) / ($n * $sum_xx - $sum_x * $sum_x);
        $intercept_quantity = ($sum_y_quantity - $slope_quantity * $sum_x) / $n;

        // Calculate forecasted quantity for 2024
        $forecast_quantity_2024 = $intercept_quantity + $slope_quantity * 6; // Assuming 2024 corresponds to 6 in the converted scale

        $forecast_total_cost_2024 = $forecast_cost_2024 * $forecast_quantity_2024;

        // Displaying the table data with forecasted cost and quantity for 2024
        $output .= "<tr>";
        $output .= "<td>" . $row['item_name'] . "</td>";
        $output .= "<td>" . formatQuantity($average_quantity_2019_to_2023) . "</td>"; // Display forecasted quantity for 2024
        $output .= "<td>" . formatQuantity($forecast_quantity_2024) . "</td>"; // Display forecasted quantity for 2024
        $output .= "<td style='text-align: right;'>" . formatCurrency($forecast_cost_2024) . "</td>"; // Display forecasted cost for 2024
        $output .= "<td style='text-align: right;'>" . formatCurrency($forecast_total_cost_2024) . "</td>"; // Display forecasted total cost for 2024
        $output .= "</tr>";
    }
} else {
    $output .= "<tr><td colspan='8'>No data available</td></tr>";
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
