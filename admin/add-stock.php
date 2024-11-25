<?php 
include 'config.php'; // Include your database configuration

if(isset($_POST['submit'])){
    // Handle stock addition
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];
    $price = $_POST['price'];
    $total = $quantity * $price;
    $type = $_POST['type']; // 1 for IN, 2 for OUT
    
    $sql = "INSERT INTO stock_list (item_id, quantity, unit, price, total, type, date_created) 
            VALUES ('$item_id', '$quantity', '$unit', '$price', '$total', '$type', NOW())";
    if($conn->query($sql)){
        echo "Stock added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h4 class="card-title">Add Stock</h4>
    </div>
    <div class="card-body">
        <form action="add_stock.php" method="POST" id="stock-form">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <label class="control-label text-info">Item</label>
                        <select name="item_id" class="form-control" required>
                            <option disabled selected>Select Item</option>
                            <?php 
                            $items = $conn->query("SELECT * FROM item_list WHERE status = 1 ORDER BY name ASC");
                            while($row = $items->fetch_assoc()):
                            ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="control-label text-info">Quantity</label>
                        <input type="number" class="form-control" name="quantity" required>
                    </div>
                    <div class="col-md-3">
                        <label class="control-label text-info">Unit</label>
                        <input type="text" class="form-control" name="unit" required>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="control-label text-info">Price</label>
                        <input type="number" class="form-control" name="price" required>
                    </div>
                    <div class="col-md-6">
                        <label class="control-label text-info">Type</label>
                        <select name="type" class="form-control" required>
                            <option value="1">Stock In</option>
                            <option value="2">Stock Out</option>
                        </select>
                    </div>
                </div>
                <div class="form-group text-center mt-3">
                    <button type="submit" name="submit" class="btn btn-flat btn-primary">Add Stock</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
// Include any additional scripts or footer content
?>
