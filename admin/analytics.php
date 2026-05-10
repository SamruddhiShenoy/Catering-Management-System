<?php
require_once '../config.php';
requireAdmin();

$page_title = 'Analytics & Reports';
$page_subtitle = 'In-depth business intelligence and charts';

require_once '../includes/header.php';
?>

<style>
/* ── Analytics-specific styles ── */
.ana-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:24px; }
.ana-stat-card {
    background: var(--surface);
    border: 1px solid var(--border2);
    border-radius: 14px;
    padding: 24px 20px 18px;
    position: relative;
    overflow: hidden;
    transition: transform .2s;
}
.ana-stat-card:hover { transform: translateY(-3px); }
.ana-stat-card::before {
    content:'';
    position:absolute; top:0; left:0; right:0; height:3px;
}
.ana-stat-card.gold::before  { background: var(--gold); }
.ana-stat-card.blue::before  { background: #5c7cfa; }
.ana-stat-card.green::before { background: var(--green); }
.ana-stat-card.purple::before{ background: #cc5de8; }

.ana-icon {
    width:44px; height:44px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:18px; margin-bottom:14px;
}
.ana-icon.gold   { background:rgba(212,168,67,.15);  color:var(--gold); }
.ana-icon.blue   { background:rgba(92,124,250,.15);  color:#5c7cfa; }
.ana-icon.green  { background:rgba(46,204,138,.15);  color:var(--green); }
.ana-icon.purple { background:rgba(204,93,232,.15);  color:#cc5de8; }

.ana-val  { font-size:28px; font-weight:800; color:var(--text); line-height:1; margin-bottom:4px; }
.ana-lbl  { font-size:12px; color:var(--text3); text-transform:uppercase; letter-spacing:.5px; margin-bottom:10px; }
.ana-chg  { font-size:12px; font-weight:600; color:var(--green); }
.ana-chg.exc { color: var(--green); }

/* Chart row */
.chart-row { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; }
.ana-card  {
    background:var(--surface); border:1px solid var(--border2);
    border-radius:14px; padding:24px;
}
.ana-card h3 { font-size:15px; font-weight:700; color:var(--text); margin-bottom:18px; }

/* Donut legend */
.donut-wrap { display:flex; align-items:center; justify-content:space-between; gap:10px; }
.donut-legend { display:flex; flex-direction:column; gap:12px; flex:1; }
.leg-row  { display:flex; align-items:center; justify-content:space-between; gap:8px; }
.leg-dot  { width:12px; height:12px; border-radius:3px; flex-shrink:0; }
.leg-name { font-size:13px; color:var(--text2); flex:1; }
.leg-pct  { font-size:13px; font-weight:700; color:var(--text); }

/* Circular progress rings */
.rings-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
.ring-box  { display:flex; flex-direction:column; align-items:center; gap:10px; }
.ring-svg  { position:relative; }
.ring-pct  { position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
             font-size:18px; font-weight:800; color:var(--text); }
.ring-name { font-size:12px; color:var(--text3); text-align:center; }

/* Revenue table tweaks */
.grow-pos { color:var(--green); font-weight:700; }
.grow-neg { color:var(--accent); font-weight:700; }
.status-dot { display:inline-block; width:7px; height:7px; border-radius:50%; background:var(--green); margin-right:5px; }
</style>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>

            <div class="content-area">

                <!-- ① TOP STAT CARDS -->
                <div class="ana-stats">
                    <div class="ana-stat-card gold">
                        <div class="ana-icon gold"><i class="fas fa-dollar-sign"></i></div>
                        <div class="ana-lbl">Total Revenue</div>
                        <div class="ana-val">$127.5K</div>
                        <div class="ana-chg"><i class="fas fa-arrow-up"></i> 18.4% MoM</div>
                    </div>
                    <div class="ana-stat-card blue">
                        <div class="ana-icon blue"><i class="fas fa-percent"></i></div>
                        <div class="ana-lbl">Occupancy Rate</div>
                        <div class="ana-val">87%</div>
                        <div class="ana-chg"><i class="fas fa-arrow-up"></i> +5% vs target</div>
                    </div>
                    <div class="ana-stat-card green">
                        <div class="ana-icon green"><i class="fas fa-star"></i></div>
                        <div class="ana-lbl">Avg Rating</div>
                        <div class="ana-val">4.9</div>
                        <div class="ana-chg exc"><i class="fas fa-circle" style="font-size:8px;"></i> Excellent</div>
                    </div>
                    <div class="ana-stat-card purple">
                        <div class="ana-icon purple"><i class="fas fa-redo"></i></div>
                        <div class="ana-lbl">Return Rate</div>
                        <div class="ana-val">72%</div>
                        <div class="ana-chg"><i class="fas fa-arrow-up"></i> +8%</div>
                    </div>
                </div>

                <!-- ② CHARTS ROW: Bar + Donut -->
                <div class="chart-row">

                    <!-- Annual Revenue Bar Chart -->
                    <div class="ana-card">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
                            <h3 style="margin:0;">📈 Annual Revenue Breakdown</h3>
                            <select style="background:var(--dark3); border:1px solid var(--border2); color:var(--text2); padding:6px 12px; border-radius:8px; font-size:12px; cursor:pointer;">
                                <option>This Year</option>
                                <option>Last Year</option>
                            </select>
                        </div>
                        <div style="position:relative; height:240px;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                        <div style="margin-top:12px; display:flex; align-items:center; gap:6px; font-size:12px; color:var(--text3);">
                            <span style="display:inline-block; width:12px; height:12px; background:var(--gold); border-radius:3px;"></span>
                            Revenue (USD)
                        </div>
                    </div>

                    <!-- Events by Category Donut -->
                    <div class="ana-card">
                        <h3>🌍 Events by Category</h3>
                        <div class="donut-wrap">
                            <div style="position:relative; width:180px; height:180px; flex-shrink:0;">
                                <canvas id="donutChart" width="180" height="180"></canvas>
                                <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; pointer-events:none;">
                                    <div style="font-size:12px; color:var(--text3);">Total</div>
                                    <div style="font-size:26px; font-weight:800; color:var(--text);">124</div>
                                </div>
                            </div>
                            <div class="donut-legend">
                                <div class="leg-row"><span class="leg-dot" style="background:#D4A843;"></span><span class="leg-name">Weddings</span><span class="leg-pct">35%</span></div>
                                <div class="leg-row"><span class="leg-dot" style="background:#5c7cfa;"></span><span class="leg-name">Corporate</span><span class="leg-pct">28%</span></div>
                                <div class="leg-row"><span class="leg-dot" style="background:#2ecc8a;"></span><span class="leg-name">Birthdays</span><span class="leg-pct">18%</span></div>
                                <div class="leg-row"><span class="leg-dot" style="background:#cc5de8;"></span><span class="leg-name">Galas</span><span class="leg-pct">12%</span></div>
                                <div class="leg-row"><span class="leg-dot" style="background:#ff7043;"></span><span class="leg-name">Other</span><span class="leg-pct">7%</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ③ TOP PERFORMING METRICS – circular rings full width -->
                <div class="ana-card" style="margin-bottom:24px;">
                    <h3>🏆 Top Performing Metrics</h3>
                    <div class="rings-row">
                        <?php
                        $metrics = [
                            ['Wedding Events',    68, '#D4A843'],
                            ['Corporate Events',  82, '#5c7cfa'],
                            ['Staff Efficiency',  91, '#2ecc8a'],
                            ['Client Satisfaction',96,'#00bcd4'],
                        ];
                        foreach($metrics as [$label, $pct, $color]):
                            $r = 54; $circ = 2 * M_PI * $r; $dash = $circ * $pct / 100; $gap = $circ - $dash;
                        ?>
                        <div class="ring-box">
                            <div class="ring-svg" style="width:130px; height:130px;">
                                <svg width="130" height="130" style="transform:rotate(-90deg);">
                                    <circle cx="65" cy="65" r="<?= $r ?>" fill="none" stroke="var(--dark3)" stroke-width="10"/>
                                    <circle cx="65" cy="65" r="<?= $r ?>" fill="none"
                                        stroke="<?= $color ?>"
                                        stroke-width="10"
                                        stroke-linecap="round"
                                        stroke-dasharray="<?= round($dash,1) ?> <?= round($gap,1) ?>"
                                        style="transition:stroke-dasharray 1s ease;"/>
                                </svg>
                                <div class="ring-pct"><?= $pct ?>%</div>
                            </div>
                            <div class="ring-name"><?= $label ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ④ DETAILED REVENUE TABLE -->
                <div class="ana-card">
                    <h3>📋 Revenue by Month (Detailed)</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>MONTH</th>
                                <th>EVENTS</th>
                                <th>REVENUE</th>
                                <th>AVG ORDER VALUE</th>
                                <th>GROWTH</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $rows = [
                                ['January 2025',  8,  '$42,000', '$5,250', '+12%'],
                                ['February 2025', 12, '$68,500', '$5,708', '+22%'],
                                ['March 2025',    10, '$55,200', '$5,520', '+8%'],
                                ['April 2025',    14, '$79,800', '$5,700', '+18%'],
                                ['May 2025',       9, '$48,600', '$5,400', '+5%'],
                                ['June 2025',     16, '$94,200', '$5,888', '+28%'],
                            ];
                            foreach($rows as [$month,$events,$rev,$avg,$growth]): ?>
                            <tr>
                                <td><?= $month ?></td>
                                <td><?= $events ?></td>
                                <td style="color:var(--gold); font-weight:600;"><?= $rev ?></td>
                                <td><?= $avg ?></td>
                                <td class="grow-pos"><?= $growth ?></td>
                                <td><span class="status-dot"></span><span style="color:var(--green); font-size:12px;">Active</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div><!-- /content-area -->
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Bar Chart ──────────────────────────────────────────────────────
const barCtx = document.getElementById('revenueChart').getContext('2d');
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const values = [32000,48000,35000,58000,42000,72000,48500,62000,45000,80000,62000,90000];

new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Revenue',
            data: values,
            backgroundColor: values.map((v,i) => i === 7 ? '#D4A843' : 'rgba(212,168,67,0.55)'),
            borderRadius: 5,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` $${(ctx.parsed.y/1000).toFixed(1)}K`
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#888', font: { size: 11 } },
                border: { display: false }
            },
            y: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: {
                    color: '#888',
                    font: { size: 11 },
                    callback: v => '$' + (v/1000) + 'K'
                },
                border: { display: false }
            }
        }
    }
});

// ── Donut Chart ────────────────────────────────────────────────────
const donutCtx = document.getElementById('donutChart').getContext('2d');
new Chart(donutCtx, {
    type: 'doughnut',
    data: {
        labels: ['Weddings','Corporate','Birthdays','Galas','Other'],
        datasets: [{
            data: [35,28,18,12,7],
            backgroundColor: ['#D4A843','#5c7cfa','#2ecc8a','#cc5de8','#ff7043'],
            borderColor: 'transparent',
            hoverOffset: 8,
            borderRadius: 4
        }]
    },
    options: {
        cutout: '65%',
        responsive: false,
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}%` } } }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
