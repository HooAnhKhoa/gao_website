<?php
// admin/customers.php
require_once '../includes/init.php';

// Check admin
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

// Xử lý Khóa/Mở khóa tài khoản
$db = Database::getInstance();
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = $_GET['action'] == 'block' ? 'inactive' : 'active';
    
    $db->update('users', ['status' => $status], "id = ?", [$id]);
    Functions::showMessage('success', 'Đã cập nhật trạng thái khách hàng!');
    header('Location: customers.php');
    exit;
}

$pageTitle = 'Quản lý khách hàng';
require_once 'includes/header.php';

// Phân trang
$page = $_GET['page'] ?? 1;
$limit = 15;
$keyword = $_GET['search'] ?? '';

$where = "role = 'user'";
$params = [];

if ($keyword) {
    $where .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search = "%$keyword%";
    $params = array_merge($params, [$search, $search, $search]);
}

$total_users = $db->selectOne("SELECT COUNT(*) as total FROM users WHERE $where", $params)['total'];
$pagination = Functions::paginate($total_users, $page, $limit);

$users = $db->select("
    SELECT * FROM users 
    WHERE $where 
    ORDER BY created_at DESC 
    LIMIT $limit OFFSET {$pagination['offset']}", 
    $params
);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Danh sách khách hàng</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Tên, Email hoặc SĐT..." value="<?php echo htmlspecialchars($keyword); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100"><i class="fas fa-search"></i> Tìm kiếm</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="table-success">
                    <tr>
                        <th width="50">ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Địa chỉ</th>
                        <th>Ngày đăng ký</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="8" class="text-center">Không tìm thấy khách hàng nào.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <span class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars(substr($user['address'] ?? '', 0, 30)); ?>...</td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['status'] == 'active'): ?>
                                    <span class="badge bg-success">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Đã khóa</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['status'] == 'active'): ?>
                                    <a href="customers.php?action=block&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-danger confirm-delete" 
                                       data-confirm="Bạn muốn khóa tài khoản này?"
                                       title="Khóa tài khoản">
                                        <i class="fas fa-lock"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="customers.php?action=unblock&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-success" 
                                       title="Mở khóa">
                                        <i class="fas fa-lock-open"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="orders.php?user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info" title="Xem lịch sử mua hàng">
                                    <i class="fas fa-history"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination['total_pages'] > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo !$pagination['has_prev'] ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $pagination['prev_page']; ?>&search=<?php echo $keyword; ?>">Trước</a>
                </li>
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $keyword; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo !$pagination['has_next'] ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&search=<?php echo $keyword; ?>">Sau</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>