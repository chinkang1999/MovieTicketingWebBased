<?php 
include "db.php";

// 1. Process Final Form Submission
if(isset($_POST['fnb_order'])){
    $quantity = intval($_POST['quantity']);
    $remarks = $conn->real_escape_string($_POST['remarks']);
    
    // Fetch values from the hidden inputs in the form
    $price = floatval($_POST['price']);
    $fnb_id = intval($_POST['fnb_id']);
    
    $fnb_total_price = $quantity * $price;
    
    $sql="INSERT INTO fnb_item(quantity, fnb_total_price, fnb_item_status, remarks, fnb_item) VALUES ($quantity, $fnb_total_price, 'Preparing', '$remarks', '$fnb_id')";
    
    if ($conn->query($sql) === TRUE) {
        $fnb_item_id = $conn->insert_id;
        if ($fnb_item_id > 0) {
            $_SESSION['fnb_item_id'] = $fnb_item_id;
            // Redirect to clear the POST data so they don't accidentally submit twice
            header("Location: update_fnb.php?tab=fnb&success=1");
            exit();
        }
    }
}

// 2. Setup standard variables
$seat_id = isset($_GET['id']) ? intval($_GET['id']) : 1; 
$showtime_id = isset($_SESSION['showtime_id']) ? intval($_SESSION['showtime_id']) : 1; 

$active_tab = (isset($_GET['tab']) && $_GET['tab'] === 'combo') ? 'combo' : 'fnb';

// Check if a specific item was clicked to reveal its form
$selected_fnb = isset($_GET['select_fnb']) ? intval($_GET['select_fnb']) : 0;
$selected_combo = isset($_GET['select_combo']) ? intval($_GET['select_combo']) : 0;

include "header.php";
?>

<!DOCTYPE html>
<html>
    <head>
        <title>CineWave - FNB</title>
        <link rel="stylesheet" href="css/styles.css">
        <link href="https://fonts.googleapis.com/css2?family=Lavishly+Yours&family=Quantico:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    </head>
    <body class="body">
        <h1>FNB</h1>
        
        <div class="auth-tabs">
            <a href="update_fnb.php?tab=combo" class="<?php echo ($active_tab === 'combo') ? 'active' : ''; ?>">Combo</a>
            <a href="update_fnb.php?tab=fnb" class="<?php echo ($active_tab === 'fnb') ? 'active' : ''; ?>">Fnb</a>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <p style="text-align:center; color:#28a745; font-weight:bold;">Item added to your order!</p>
        <?php endif; ?>

        <section class="container">
            <?php if ($active_tab === 'fnb'): 
                $sql = "SELECT * FROM fnb";
                $result = $conn->query($sql);
                while($row = $result->fetch_assoc()){ 
            ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($row['path']); ?>" alt="<?php echo htmlspecialchars($row['fnb_name']); ?>" height="200">
                    <h3><?php echo htmlspecialchars($row['fnb_name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <h5>$<?php echo number_format($row['fnb_price'], 2); ?></h5>
                    
                    <?php if ($selected_fnb === intval($row['fnb_id'])): ?>
                        <!-- Form reveals only if this specific item's Add button was clicked -->
                        <form method="POST" action="update_fnb.php?tab=fnb">
                            <!-- Hidden inputs send the exact price and ID to the top of the page -->
                            <input type="hidden" name="fnb_id" value="<?php echo $row['fnb_id']; ?>">
                            <input type="hidden" name="price" value="<?php echo $row['fnb_price']; ?>">
                            
                            <h3 style="font-size: 1rem; margin-top: 10px;">Quantity</h3>
                            <input type="number" name="quantity" value="1" min="1" required style="width: 100%; padding: 5px;">
                            
                            <h3 style="font-size: 1rem;">Remarks</h3>
                            <input type="text" name="remarks" placeholder="Optional notes" style="width: 100%; padding: 5px;">
                            
                            <button type="submit" name="fnb_order" class="btn" style="margin-top: 10px; width: 100%;">Confirm Order</button>
                        </form>
                    <?php else: ?>
                        <!-- Initial Add Button acts as a link to reveal the form for this ID -->
                        <a href="update_fnb.php?tab=fnb&select_fnb=<?php echo $row['fnb_id']; ?>" class="btn" style="display:block; text-align:center;">Add</a>
                    <?php endif; ?>
                </div>
            <?php } ?>
            
            <?php else: 
                // COMBO TAB LOGIC (mirrors the FNB logic)
                $sql = "SELECT * FROM combo";
                $result = $conn->query($sql);
                while($row = $result->fetch_assoc()){ 
            ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($row['path']); ?>" alt="<?php echo htmlspecialchars($row['combo_name']); ?>" height="200">
                    <h3><?php echo htmlspecialchars($row['combo_name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <h5>$<?php echo number_format($row['combo_price'], 2); ?></h5>
                    
                    <?php if ($selected_combo === intval($row['combo_id'])): ?>
                        <!-- You can duplicate the form processing logic at the top of the script for combo_order -->
                        <form method="POST" action="update_fnb.php?tab=combo">
                            <input type="hidden" name="combo_id" value="<?php echo $row['combo_id']; ?>">
                            <input type="hidden" name="price" value="<?php echo $row['combo_price']; ?>">
                            <input type="number" name="quantity" value="1" min="1" required style="width: 100%; padding: 5px;">
                            <input type="text" name="remarks" placeholder="Optional notes" style="width: 100%; padding: 5px; margin-top: 10px;">
                            <button type="submit" name="combo_order" class="btn" style="margin-top: 10px; width: 100%;">Confirm Combo</button>
                        </form>
                    <?php else: ?>
                        <a href="update_fnb.php?tab=combo&select_combo=<?php echo $row['combo_id']; ?>" class="btn" style="display:block; text-align:center;">Add</a>
                    <?php endif; ?>
                </div>
            <?php } ?>
            <?php endif; ?>
        </section>
        
        <div style="text-align:center; margin-top: 20px;">
            <a href="payment.php?id=<?php echo $seat_id; ?>" class="btn">Next: Payment</a>
        </div>
    </body>
    <?php include "footer.html"; ?>
</html>