<?php
include 'db_connect.php'; // Make sure this file contains your DB connection details

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $new_item_name = $_POST['new_item_name'];
    $new_item_description = $_POST['new_item_description'];
    $new_item_supplier = $_POST['new_item_supplier'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];
    $price = $_POST['price'];
    $type = $_POST['type'];

    // Check if item already exists
    $item_query = $conn->query("SELECT id FROM item_list WHERE name = '$new_item_name' LIMIT 1");
    
    if ($item_query->num_rows > 0) {
        // If item exists, fetch the item_id
        $item = $item_query->fetch_assoc();
        $item_id = $item['id'];
    } else {
        // Insert new item into item_list
        $insert_item = $conn->query("INSERT INTO item_list (name, description, supplier_id) VALUES ('$new_item_name', '$new_item_description', '$new_item_supplier')");
        
        if ($insert_item) {
            $item_id = $conn->insert_id;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add new item']);
            exit;
        }
    }

    // Insert the stock into the stock_list
    $total = $quantity * $price;  // Calculate total price
    $insert_stock = $conn->query("INSERT INTO stock_list (item_id, quantity, unit, price, total, type) VALUES ('$item_id', '$quantity', '$unit', '$price', '$total', '$type')");
    
    if ($insert_stock) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add stock']);
    }
}
?>



<!-- Add Stock Button -->
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Stocks</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-flat btn-primary" data-toggle="modal" data-target="#addStockModal">
                <span class="fas fa-plus"></span> Add Stock
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <div class="container-fluid">
                <table class="table table-bordered table-stripped">
                    <colgroup>
                        <col width="5%">
                        <col width="20%">
                        <col width="20%">
                        <col width="40%">
                        <col width="15%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Supplier</th>
                            <th>Description</th>
                            <th>Available Stocks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $qry = $conn->query("SELECT i.*, s.name as supplier FROM `item_list` i INNER JOIN supplier_list s ON i.supplier_id = s.id ORDER BY `name` DESC");
                        while ($row = $qry->fetch_assoc()):
                            $in = $conn->query("SELECT SUM(quantity) as total FROM stock_list WHERE item_id = '{$row['id']}' AND type = 1")->fetch_array()['total'];
                            $out = $conn->query("SELECT SUM(quantity) as total FROM stock_list WHERE item_id = '{$row['id']}' AND type = 2")->fetch_array()['total'];
                            $row['available'] = $in - $out;
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['supplier']; ?></td>
                                <td><?php echo $row['description']; ?></td>
                                <td class="text-right"><?php echo number_format($row['available']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding Stock -->
<div class="modal fade" id="addStockModal" tabindex="-1" role="dialog" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form to Add Stock -->
                <form id="addStockForm">
                    <div class="form-group">
                        <label for="new_item_name">New Item Name</label>
                        <input type="text" class="form-control" id="new_item_name" name="new_item_name" required>
                    </div>
                    <div class="form-group">
                        <label for="new_item_description">Description</label>
                        <input type="text" class="form-control" id="new_item_description" name="new_item_description" required>
                    </div>
                    <div class="form-group">
                        <label for="new_item_supplier">Supplier</label>
                        <select class="form-control" id="new_item_supplier" name="new_item_supplier" required>
                            <option value="">Select Supplier</option>
                            <?php
                            $suppliers = $conn->query("SELECT id, name FROM supplier_list");
                            while ($supplier = $suppliers->fetch_assoc()):
                            ?>
                                <option value="<?php echo $supplier['id']; ?>"><?php echo $supplier['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="unit">Unit</label>
                        <input type="text" class="form-control" id="unit" name="unit">
                    </div>
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Stock Type</label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="1">In</option>
                            <option value="2">Out</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#addStockForm').submit(function(e){
        e.preventDefault();  // Prevent the default form submission

        var formData = $(this).serialize();  // Serialize form data

        $.ajax({
            url: 'add_stock_process.php', // PHP script to handle adding stock
            method: 'POST',
            data: formData,
            dataType: 'json', // Expecting JSON response
            success: function(response){
                if(response.status == 'success'){
                    alert('Stock added successfully!');
                    $('#addStockModal').modal('hide'); // Close the modal on success
                    location.reload(); // Refresh the page to show updated list
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(err){
                alert('An error occurred');
                console.log(err); // Log any errors to the console for debugging
            }
        });
    });
});
</script>
