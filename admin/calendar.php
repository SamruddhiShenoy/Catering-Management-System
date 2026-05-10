<?php
require_once '../config.php';
requireAdmin();

$page_title = 'Event Calendar';
$page_subtitle = 'Visual schedule of all upcoming catering events.';

// Fetch all bookings for the calendar
$stmt = $pdo->query("SELECT * FROM bookings ORDER BY date ASC");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map bookings to dates for easy access
$event_map = [];
foreach ($bookings as $b) {
    $event_map[$b['date']][] = $b;
}

require_once '../includes/header.php';

// Simple calendar logic
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
$number_of_days = date('t', $first_day_of_month);
$date_components = getdate($first_day_of_month);
$month_name = $date_components['month'];
$day_of_week = $date_components['wday'];

$prev_month = $month == 1 ? 12 : $month - 1;
$prev_year = $month == 1 ? $year - 1 : $year;
$next_month = $month == 12 ? 1 : $month + 1;
$next_year = $month == 12 ? $year + 1 : $year;
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="table-card reveal">
                    <div class="card-header">
                        <div style="display:flex; align-items:center; gap:20px;">
                            <h3 style="font-size:24px;"><?= $month_name ?> <?= $year ?></h3>
                            <div style="display:flex; gap:8px;">
                                <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>" class="btn btn-ghost btn-sm"><i class="fas fa-chevron-left"></i></a>
                                <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>" class="btn btn-ghost btn-sm"><i class="fas fa-chevron-right"></i></a>
                                <a href="calendar.php" class="btn btn-ghost btn-sm">Today</a>
                            </div>
                        </div>
                        <a href="bookings.php" class="btn btn-gold btn-sm"><i class="fas fa-plus"></i> New Event</a>
                    </div>
                    
                    <div class="calendar-grid">
                        <?php 
                        $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                        foreach($days as $d) echo "<div class='cal-day-header'>$d</div>";
                        
                        // Empty cells before first day
                        for($i=0; $i<$day_of_week; $i++) echo "<div></div>";
                        
                        // Days of the month
                        for($d=1; $d<=$number_of_days; $d++) {
                            $current_date = sprintf("%04d-%02d-%02d", $year, $month, $d);
                            $is_today = $current_date == date('Y-m-d');
                            $has_events = isset($event_map[$current_date]);
                            ?>
                            <div class="cal-day <?= $is_today ? 'today' : '' ?> <?= $has_events ? 'has-event' : '' ?>">
                                <div class="cal-day-num"><?= $d ?></div>
                                <?php if($has_events): ?>
                                    <?php foreach($event_map[$current_date] as $ev): ?>
                                        <div class="cal-event <?= $ev['status'] === 'confirmed' ? 'green' : 'blue' ?>" title="<?= htmlspecialchars($ev['event']) ?>">
                                            <?= htmlspecialchars($ev['event']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>

                <div class="table-card mt-28 reveal">
                    <div class="card-header">
                        <h3>📋 Upcoming Events List</h3>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Event</th>
                                <th>Venue</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $upcoming = array_filter($bookings, function($b) {
                                return $b['date'] >= date('Y-m-d');
                            });
                            foreach(array_slice($upcoming, 0, 8) as $b): 
                            ?>
                                <tr>
                                    <td style="color:var(--gold); font-weight:600;"><?= $b['date'] ?></td>
                                    <td><strong><?= htmlspecialchars($b['event']) ?></strong></td>
                                    <td><?= htmlspecialchars($b['venue']) ?></td>
                                    <td><span class="status-badge <?= $b['status'] ?>"><?= $b['status'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
