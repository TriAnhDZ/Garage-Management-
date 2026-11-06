<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-chart-line" style="color: #4834d4;"></i>
                        <span>Báo cáo doanh thu trên tổng khách hàng</span>
                    </h3>
                    <small style="color: #747d8c;">
                        Thống kê chi tiết doanh thu và tỉ trọng đóng góp theo từng khách hàng trong tháng
                    </small>
                </div>

                <!-- Chọn tháng và năm -->
                <form method="GET" action="index.php" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                    <input type="hidden" name="c" value="report">
                    <input type="hidden" name="a" value="revenue">
                    
                    <label style="font-weight: 600; margin: 0;">Tháng:</label>
                    <select name="month" class="form-control" style="width: 80px; padding: 4px 8px;">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($month == $m) ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endfor; ?>
                    </select>

                    <label style="font-weight: 600; margin: 0;">Năm:</label>
                    <select name="year" class="form-control" style="width: 100px; padding: 4px 8px;">
                        <?php for ($y = 2023; $y <= 2027; $y++): ?>
                            <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>

                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-sync-alt"></i> Xem báo cáo
                    </button>
                </form>
            </div>

            <div class="card-content">
                <!-- 4 Thẻ KPI Tổng quan tháng -->
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-12 col-m-6 col-m-3">
                        <div class="stats-card bg-success" style="margin-bottom: 10px;">
                            <div class="stats-icon"><i class="fas fa-coins"></i></div>
                            <div class="stats-info">
                                <h3 style="font-size: 1.35rem;"><?= number_format($report['total_revenue']) ?> đ</h3>
                                <p>Tổng doanh thu tháng <?= $month ?>/<?= $year ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-m-6 col-m-3">
                        <div class="stats-card bg-primary" style="margin-bottom: 10px;">
                            <div class="stats-icon"><i class="fas fa-users"></i></div>
                            <div class="stats-info">
                                <h3 style="font-size: 1.35rem;"><?= $report['total_customers'] ?> khách</h3>
                                <p>Tổng khách hàng có doanh thu</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-m-6 col-m-3">
                        <div class="stats-card bg-warning" style="margin-bottom: 10px;">
                            <div class="stats-icon"><i class="fas fa-tools"></i></div>
                            <div class="stats-info">
                                <h3 style="font-size: 1.35rem;"><?= $report['total_repairs'] ?> lượt</h3>
                                <p>Tổng số lượt xe sửa chữa</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-m-6 col-m-3">
                        <div class="stats-card bg-danger" style="margin-bottom: 10px;">
                            <div class="stats-icon"><i class="fas fa-receipt"></i></div>
                            <div class="stats-info">
                                <h3 style="font-size: 1.35rem;"><?= number_format($report['avg_per_customer']) ?> đ</h3>
                                <p>Doanh thu TB / khách hàng</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Bảng chi tiết doanh thu theo khách hàng -->
                    <div class="col-12 col-m-8">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h4 style="margin: 0; color: #4834d4; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-user-check"></i>
                                <span>Chi tiết doanh thu trên tổng khách hàng:</span>
                            </h4>
                            <span class="badge badge-primary">
                                Có <?= count($report['customer_details']) ?> khách hàng phát sinh
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table-custom table-hover">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Khách hàng</th>
                                        <th>Số điện thoại / Email</th>
                                        <th>Phương tiện / Biển số</th>
                                        <th>Lượt sửa</th>
                                        <th>Thành tiền (VNĐ)</th>
                                        <th style="min-width: 140px;">Tỉ lệ (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($report['customer_details'])): ?>
                                        <tr>
                                            <td colspan="7" style="text-align: center; padding: 30px; color: #747d8c;">
                                                Tháng <?= $month ?>/<?= $year ?> chưa phát sinh doanh thu từ khách hàng nào.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $stt = 1; ?>
                                        <?php foreach ($report['customer_details'] as $c): ?>
                                            <tr>
                                                <td><?= $stt++ ?></td>
                                                <td>
                                                    <b style="font-size: 1rem; color: #130f40;">
                                                        <?= htmlspecialchars($c['ten_khach_hang']) ?>
                                                    </b>
                                                    <br>
                                                    <small style="color: #747d8c;">Mã KH: #<?= $c['ma_kh'] ?></small>
                                                </td>
                                                <td>
                                                    <span style="color: #1a73e8; font-weight: 500;">
                                                        <i class="fas fa-phone-alt"></i> <?= htmlspecialchars($c['sdt']) ?>
                                                    </span>
                                                    <br>
                                                    <small style="color: #747d8c;"><?= htmlspecialchars($c['email']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info" style="font-size: 0.85rem;">
                                                        <?= htmlspecialchars($c['danh_sach_xe'] ?? 'Chưa rõ') ?>
                                                    </span>
                                                </td>
                                                <td style="text-align: center;">
                                                    <span class="badge badge-primary" style="font-size: 0.9rem; padding: 4px 10px;">
                                                        <?= $c['so_luot_sua'] ?>
                                                    </span>
                                                </td>
                                                <td style="font-weight: 700; color: #2ed573; font-size: 1.05rem;">
                                                    <?= number_format((float)$c['thanh_tien']) ?> đ
                                                </td>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <div style="flex: 1; background: #e2e8f0; height: 10px; border-radius: 5px; overflow: hidden;">
                                                            <div style="background: #2ed573; height: 100%; width: <?= $c['ti_le'] ?>%;"></div>
                                                        </div>
                                                        <span style="font-weight: 700; color: #4834d4; width: 45px;"><?= $c['ti_le'] ?>%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr style="background: var(--second-bg-color, #f1f2f6); font-weight: 700;">
                                            <td colspan="4" style="text-align: right;">TỔNG CỘNG DOANH THU:</td>
                                            <td style="text-align: center; color: #4834d4;"><?= $report['total_repairs'] ?> lượt</td>
                                            <td style="color: #2ed573; font-size: 1.1rem;"><?= number_format($report['total_revenue']) ?> đ</td>
                                            <td style="color: #4834d4;">100%</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Biểu đồ Chart.js tỉ trọng doanh thu theo khách hàng -->
                    <div class="col-12 col-m-4">
                        <h4 style="margin-top: 0; color: #4834d4; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-chart-pie"></i>
                            <span>Biểu đồ tỉ trọng doanh thu theo khách hàng:</span>
                        </h4>
                        <div style="position: relative; height: 280px; background: #ffffff; padding: 15px; border: 1px solid #edf2f7; border-radius: 8px;">
                            <canvas id="customerRevenueChart"></canvas>
                        </div>

                        <!-- Thống kê phụ: Phân bổ theo hiệu xe tham khảo -->
                        <?php if (!empty($report['brand_details'])): ?>
                            <div style="margin-top: 20px; background: var(--second-bg-color, #f8f9fa); padding: 12px; border-radius: 8px;">
                                <h5 style="margin: 0 0 10px; color: #747d8c; font-size: 0.88rem; text-transform: uppercase;">
                                    <i class="fas fa-car"></i> Doanh số phân bổ theo hiệu xe:
                                </h5>
                                <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.88rem;">
                                    <?php foreach ($report['brand_details'] as $b): ?>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span><b><?= htmlspecialchars($b['hang_xe']) ?></b> (<?= $b['so_luot_sua'] ?> lượt)</span>
                                            <span style="font-weight: 600; color: #4834d4;"><?= number_format((float)$b['thanh_tien']) ?> đ (<?= $b['ti_le'] ?>%)</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php
        $customerLabels = [];
        $customerData = [];
        foreach ($report['customer_details'] as $item) {
            $customerLabels[] = $item['ten_khach_hang'];
            $customerData[] = (float)$item['thanh_tien'];
        }
    ?>
    const labels = <?= json_encode($customerLabels, JSON_UNESCAPED_UNICODE) ?>;
    const data = <?= json_encode($customerData) ?>;

    const ctx = document.getElementById('customerRevenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels.length ? labels : ['Chưa có dữ liệu'],
            datasets: [{
                data: data.length ? data : [1],
                backgroundColor: [
                    '#4834d4', '#2ed573', '#ffa502', '#ff4757', '#1e90ff',
                    '#a55eea', '#778ca3', '#20bf6b', '#eb3b5a', '#fa8231'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { 
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    fontSize: 11
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        const idx = tooltipItem.index;
                        const label = data.labels[idx] || '';
                        const val = data.datasets[0].data[idx] || 0;
                        return label + ': ' + Number(val).toLocaleString('vi-VN') + ' đ';
                    }
                }
            }
        }
    });
});
</script>
