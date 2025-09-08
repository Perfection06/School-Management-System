<?php
include('database_connection.php');

// Check if the user is logged in and is a Student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

// Fetch student username from session
$username = $_SESSION['user']['username'];

// Securely fetch the latest student details using prepared statements
$stmt = $conn->prepare("SELECT * FROM fee_registration WHERE student_username = ? ORDER BY payment_date DESC LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// Fetch all months paid by the student
$stmt = $conn->prepare("SELECT months_paid FROM fee_registration WHERE student_username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$all_payments = $stmt->get_result();
$months_paid = [];
while ($row = $all_payments->fetch_assoc()) {
    $months_paid = array_merge($months_paid, explode(',', $row["months_paid"]));
}
$stmt->close();
$months_paid = array_unique($months_paid);
$all_months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
$paid_count = count($months_paid);
$progress_percentage = round(($paid_count / count($all_months)) * 100, 1);

// Fetch all receipts for the student
$stmt = $conn->prepare("SELECT * FROM fee_registration WHERE student_username = ? AND receipt_no IS NOT NULL");
$stmt->bind_param('s', $username);
$stmt->execute();
$receipts = $stmt->get_result();
$stmt->close();

// Fetch other payments
$stmt = $conn->prepare("SELECT payment_name, status FROM student_payments JOIN other_payments ON student_payments.payment_id = other_payments.id WHERE student_payments.student_username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$other_payments_result = $stmt->get_result();
$other_payments = [];
while ($row = $other_payments_result->fetch_assoc()) {
    $other_payments[] = $row;
}
$stmt->close();
?>

<div class="bg-white rounded-2xl shadow-lg p-6 transition-all hover:shadow-xl hover:-translate-y-1 duration-300 animate-fade-in backdrop-blur-sm bg-opacity-80">
    <div class="flex items-center gap-3 mb-4">
        <i class='bx bx-credit-card text-indigo-600 text-3xl'></i>
        <h2 class="text-lg font-bold text-gray-800">Fee Overview</h2>
    </div>

    <!-- Student Info -->
    <div class="bg-gray-50 rounded-lg p-4 mb-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Student Details</h3>
        <?php if ($student): ?>
            <p class="text-sm"><span class="font-medium">Name:</span> <?= htmlspecialchars($student["student_name"]) ?></p>
            <p class="text-sm"><span class="font-medium">Class:</span> <?= htmlspecialchars($student["class"]) ?></p>
            <p class="text-sm"><span class="font-medium">Parent Phone:</span> <?= htmlspecialchars($student["parent_phone"]) ?></p>
            <p class="text-sm"><span class="font-medium">Username:</span> <?= htmlspecialchars($student["student_username"]) ?></p>
        <?php else: ?>
            <p class="text-sm text-gray-500">No student details available.</p>
        <?php endif; ?>
    </div>

    <!-- Payment Progress -->
    <h3 class="text-sm font-semibold text-gray-700 mb-2">Payment Progress</h3>
    <div class="relative w-24 h-24 mx-auto mb-4">
        <svg class="w-full h-full" viewBox="0 0 36 36">
            <path d="M18 2.0845
                a 15.9155 15.9155 0 0 1 0 31.831
                a 15.9155 15.9155 0 0 1 0 -31.831"
                fill="none" stroke="#e5e7eb" stroke-width="2" />
            <path d="M18 2.0845
                a 15.9155 15.9155 0 0 1 0 31.831
                a 15.9155 15.9155 0 0 1 0 -31.831"
                fill="none" stroke="#4f46e5" stroke-width="2"
                stroke-dasharray="<?= $progress_percentage ?>, 100" />
            <text x="18" y="20" text-anchor="middle" fill="#4f46e5" font-size="8" font-weight="bold"><?= $progress_percentage ?>%</text>
        </svg>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4">
        <?php foreach ($all_months as $month): ?>
            <div class="py-1 text-center rounded-lg text-white font-medium text-xs
                <?= in_array($month, $months_paid) 
                    ? 'bg-green-500 animate-pulse' 
                    : 'bg-gray-400' ?> hover:scale-105 transition-all">
                <?= $month ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Receipts -->
    <h3 class="text-sm font-semibold text-gray-700 mb-2">Receipts Archive</h3>
    <div class="mb-4">
        <select class="w-full p-1 border rounded-lg text-sm" onchange="filterReceipts(this.value)">
            <option value="all">All Years</option>
            <?php for ($year = date('Y'); $year >= date('Y') - 5; $year--): ?>
                <option value="<?= $year ?>"><?= $year ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="space-y-2 mb-4 max-h-40 overflow-y-auto">
        <?php if ($receipts->num_rows > 0): ?>
            <?php while ($r = $receipts->fetch_assoc()): ?>
                <div class="flex justify-between items-center bg-gray-50 p-2 rounded-lg border hover:bg-gray-100 transition-all">
                    <span class="font-medium text-sm">Receipt: <?= htmlspecialchars($r["receipt_no"]) ?></span>
                    <div class="flex gap-2">
                        <a href="<?= htmlspecialchars($r["receipt_no"]) ?>" target="_blank"
                           class="text-indigo-600 hover:text-indigo-800 hover:underline text-sm">View</a>
                        <button class="text-indigo-600 hover:text-indigo-800 text-sm">Download</button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-500 text-sm">No receipts available.</p>
        <?php endif; ?>
    </div>

    <!-- Other Payments -->
    <h3 class="text-sm font-semibold text-gray-700 mb-2">Other Payments</h3>
    <div class="space-y-2 max-h-40 overflow-y-auto">
        <?php if (!empty($other_payments)): ?>
            <?php foreach ($other_payments as $p): ?>
                <div class="flex justify-between items-center bg-gray-50 p-2 rounded-lg border hover:bg-gray-100 transition-all">
                    <span class="text-sm"><?= htmlspecialchars($p["payment_name"]) ?></span>
                    <span class="text-sm <?= $p["status"] === 'Paid' ? 'text-green-600 font-medium' : 'text-red-600 font-medium' ?>">
                        <?= htmlspecialchars($p["status"]) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500 text-sm">No other payments recorded.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function filterReceipts(year) {
    // Placeholder for filtering receipts by year
    console.log('Filtering receipts for year:', year);
}
</script>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.5s ease-in-out;
    }
</style>