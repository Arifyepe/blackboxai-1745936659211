<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check if user is admin or the order belongs to the logged-in user
if (!isset($_GET['id'])) {
    setFlash("Order ID is required", "error");
    redirect("/");
}

$order_id = (int)$_GET['id'];

try {
    // Get order details with customer information
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as customer_name, u.email, u.phone, u.address
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        setFlash("Order not found", "error");
        redirect("/");
    }

    // Check authorization
    if (!isAdmin() && $order['user_id'] !== $_SESSION['user_id']) {
        setFlash("Unauthorized access", "error");
        redirect("/");
    }

    // Get order items with product details
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.category, p.subcategory
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();

    // Get or generate invoice number
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $invoice = $stmt->fetch();

    if (!$invoice) {
        // Generate new invoice
        $invoice_number = generateInvoiceNumber();
        $stmt = $pdo->prepare("INSERT INTO invoices (order_id, invoice_number) VALUES (?, ?)");
        $stmt->execute([$order_id, $invoice_number]);
        
        $invoice = [
            'invoice_number' => $invoice_number,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

} catch (PDOException $e) {
    setFlash("Error generating invoice", "error");
    redirect("/");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $invoice['invoice_number']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .print-break-inside-avoid {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Print Button -->
    <div class="container mx-auto px-4 py-6 no-print">
        <button onclick="window.print()" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">
            <i class="fas fa-print mr-2"></i> Print Invoice
        </button>
    </div>

    <!-- Invoice -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8">
            <!-- Header -->
            <div class="border-b pb-8 mb-8">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-800">INVOICE</h1>
                        <p class="text-gray-600 mt-2">Invoice #: <?php echo $invoice['invoice_number']; ?></p>
                        <p class="text-gray-600">Date: <?php echo date('F j, Y', strtotime($invoice['created_at'])); ?></p>
                    </div>
                    <div class="text-right">
                        <h2 class="text-2xl font-bold text-red-600">Sports Store</h2>
                        <p class="text-gray-600">123 Sports Street</p>
                        <p class="text-gray-600">City, Country 12345</p>
                        <p class="text-gray-600">Phone: (123) 456-7890</p>
                        <p class="text-gray-600">Email: info@sportsstore.com</p>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="grid grid-cols-2 gap-8 mb-8 print-break-inside-avoid">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Bill To:</h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p class="text-gray-600"><?php echo htmlspecialchars($order['email']); ?></p>
                    <p class="text-gray-600"><?php echo htmlspecialchars($order['phone']); ?></p>
                    <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Order Details:</h3>
                    <p class="text-gray-600">Order #: <?php echo $order['id']; ?></p>
                    <p class="text-gray-600">Order Date: <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                    <p class="text-gray-600">Payment Method: <?php echo ucfirst($order['payment_method']); ?></p>
                    <p class="text-gray-600">Status: <?php echo ucfirst($order['status']); ?></p>
                </div>
            </div>

            <!-- Order Items -->
            <div class="mb-8 print-break-inside-avoid">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Item</th>
                            <th class="text-left py-2">Category</th>
                            <th class="text-right py-2">Price</th>
                            <th class="text-right py-2">Quantity</th>
                            <th class="text-right py-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr class="border-b">
                            <td class="py-4"><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td class="py-4">
                                <?php echo ucfirst($item['category']); ?> / <?php echo ucfirst($item['subcategory']); ?>
                            </td>
                            <td class="py-4 text-right"><?php echo formatPrice($item['price']); ?></td>
                            <td class="py-4 text-right"><?php echo $item['quantity']; ?></td>
                            <td class="py-4 text-right"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="py-4 text-right font-bold">Total:</td>
                            <td class="py-4 text-right font-bold"><?php echo formatPrice($order['total_amount']); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Terms and Notes -->
            <div class="border-t pt-8 print-break-inside-avoid">
                <div class="grid grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Terms & Conditions:</h3>
                        <ul class="text-gray-600 text-sm list-disc list-inside">
                            <li>Payment is due upon receipt of invoice</li>
                            <li>Returns accepted within 14 days of purchase</li>
                            <li>Items must be in original condition</li>
                            <li>Shipping costs are non-refundable</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Notes:</h3>
                        <p class="text-gray-600 text-sm">
                            Thank you for shopping with Sports Store! If you have any questions about this invoice, 
                            please contact our customer service.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
