<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    if ($action === "add") {

        $category_id = intval($_POST["category_id"]);
        $seller_id = !empty($_POST["seller_id"]) ? intval($_POST["seller_id"]) : null;
        $name = trim($_POST["name"]);
        $description = trim($_POST["description"]);
        $price = floatval($_POST["price"]);
        $stock = intval($_POST["stock"]);
        $status = $_POST["status"] ?? "active";

        $image = "";

        if (!empty($_FILES["image"]["name"])) {

            $upload_dir = "../assets/images/";

            $file_name = time() . "_" . basename($_FILES["image"]["name"]);

            $target_file = $upload_dir . $file_name;

            $allowed_types = [
                "image/jpeg",
                "image/png",
                "image/webp",
                "image/jpg"
            ];

            if (in_array($_FILES["image"]["type"], $allowed_types)) {

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image = $file_name;
                }

            }

        }

        $stmt = $conn->prepare("
            INSERT INTO products
            (category_id, seller_id, name, description, price, stock, image, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iissdiss",
            $category_id,
            $seller_id,
            $name,
            $description,
            $price,
            $stock,
            $image,
            $status
        );

        if ($stmt->execute()) {
            $message = "Product added successfully.";
            $message_type = "success";
        } else {
            $message = "Unable to add product.";
            $message_type = "error";
        }
    }


    if ($action === "update") {

        $product_id = intval($_POST["product_id"]);
        $category_id = intval($_POST["category_id"]);
        $seller_id = !empty($_POST["seller_id"]) ? intval($_POST["seller_id"]) : null;
        $name = trim($_POST["name"]);
        $description = trim($_POST["description"]);
        $price = floatval($_POST["price"]);
        $stock = intval($_POST["stock"]);
        $status = $_POST["status"] ?? "active";

        $image = $_POST["existing_image"] ?? "";

        if (!empty($_FILES["image"]["name"])) {

            $upload_dir = "../assets/images/";

            $file_name = time() . "_" . basename($_FILES["image"]["name"]);

            $target_file = $upload_dir . $file_name;

            $allowed_types = [
                "image/jpeg",
                "image/png",
                "image/webp",
                "image/jpg"
            ];

            if (in_array($_FILES["image"]["type"], $allowed_types)) {

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {

                    $image = $file_name;

                }

            }

        }

        $stmt = $conn->prepare("
            UPDATE products
            SET
                category_id = ?,
                seller_id = ?,
                name = ?,
                description = ?,
                price = ?,
                stock = ?,
                image = ?,
                status = ?
            WHERE product_id = ?
        ");

        $stmt->bind_param(
            "iissdissi",
            $category_id,
            $seller_id,
            $name,
            $description,
            $price,
            $stock,
            $image,
            $status,
            $product_id
        );

        if ($stmt->execute()) {
            $message = "Product updated successfully.";
            $message_type = "success";
        } else {
            $message = "Unable to update product.";
            $message_type = "error";
        }
    }


    if ($action === "delete") {

        $product_id = intval($_POST["product_id"]);

        $stmt = $conn->prepare("
            DELETE FROM products
            WHERE product_id = ?
        ");

        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            $message = "Product deleted successfully.";
            $message_type = "success";
        } else {
            $message = "Unable to delete product.";
            $message_type = "error";
        }
    }
}


$search = trim($_GET["search"] ?? "");
$category_filter = intval($_GET["category"] ?? 0);
$status_filter = $_GET["status"] ?? "";


$categories = $conn->query("
    SELECT category_id, name
    FROM categories
    ORDER BY name ASC
");


$sellers = $conn->query("
    SELECT user_id, name
    FROM users
    WHERE role = 'seller'
    ORDER BY name ASC
");


$query = "
    SELECT
        p.product_id,
        p.name,
        p.description,
        p.price,
        p.stock,
        p.image,
        p.status,
        p.created_at,
        c.name AS category_name,
        u.name AS seller_name
    FROM products p
    LEFT JOIN categories c
        ON p.category_id = c.category_id
    LEFT JOIN users u
        ON p.seller_id = u.user_id
    WHERE 1 = 1
";


$params = [];
$types = "";


if ($search !== "") {

    $query .= "
        AND (
            p.name LIKE ?
            OR p.description LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ss";
}


if ($category_filter > 0) {

    $query .= " AND p.category_id = ? ";

    $params[] = $category_filter;

    $types .= "i";
}


if ($status_filter !== "") {

    $query .= " AND p.status = ? ";

    $params[] = $status_filter;

    $types .= "s";
}


$query .= " ORDER BY p.product_id DESC";


$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$products = $stmt->get_result();


$edit_product = null;

if (isset($_GET["edit"])) {

    $edit_id = intval($_GET["edit"]);

    $stmt = $conn->prepare("
        SELECT *
        FROM products
        WHERE product_id = ?
    ");

    $stmt->bind_param("i", $edit_id);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $edit_product = $result->fetch_assoc();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Products | ShopSphere Admin</title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body class="admin-body">

<?php include "includes/sidebar.php"; ?>

<div class="admin-main">

<?php include "includes/topbar.php"; ?>


<main class="admin-content">


    <div class="admin-page-header">

        <div>

            <span class="admin-breadcrumb">
                Administration / Products
            </span>

            <h1>Products</h1>

            <p>
                Manage ShopSphere products, stock and product information.
            </p>

        </div>

        <div>

            <button
                type="button"
                class="admin-primary-button"
                onclick="openProductForm()"
            >
                + Add Product
            </button>

        </div>

    </div>


    <?php if ($message !== ""): ?>

        <div class="admin-alert <?php echo $message_type; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <section class="admin-panel">


        <div class="product-toolbar">


            <form method="GET" class="product-filter-form">


                <div class="admin-filter-search">

                    <span>🔍</span>

                    <input
                        type="text"
                        name="search"
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search products..."
                    >

                </div>


                <select name="category">

                    <option value="">
                        All Categories
                    </option>

                    <?php if ($categories): ?>

                        <?php while ($category = $categories->fetch_assoc()): ?>

                            <option
                                value="<?php echo $category["category_id"]; ?>"
                                <?php echo $category_filter == $category["category_id"] ? "selected" : ""; ?>
                            >
                                <?php echo htmlspecialchars($category["name"]); ?>
                            </option>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </select>


                <select name="status">

                    <option value="">
                        All Status
                    </option>

                    <option
                        value="active"
                        <?php echo $status_filter === "active" ? "selected" : ""; ?>
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        <?php echo $status_filter === "inactive" ? "selected" : ""; ?>
                    >
                        Inactive
                    </option>

                </select>


                <button
                    type="submit"
                    class="filter-button"
                >
                    Filter
                </button>


                <a
                    href="products.php"
                    class="clear-filter"
                >
                    Clear
                </a>


            </form>


            <div class="product-count">

                <?php echo $products->num_rows; ?> products

            </div>


        </div>


        <div class="admin-table-wrapper">


            <table class="admin-table product-table">


                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Product</th>

                        <th>Category</th>

                        <th>Seller</th>

                        <th>Price</th>

                        <th>Stock</th>

                        <th>Status</th>

                        <th>Created</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>


                <?php if ($products->num_rows > 0): ?>


                    <?php while ($product = $products->fetch_assoc()): ?>


                        <tr>


                            <td>

                                <strong>
                                    #<?php echo $product["product_id"]; ?>
                                </strong>

                            </td>


                            <td>

                                <div class="product-table-info">


                                    <div class="product-table-image">

                                        <?php if (!empty($product["image"])): ?>

                                            <img
                                                src="../assets/images/<?php echo htmlspecialchars($product["image"]); ?>"
                                                alt="<?php echo htmlspecialchars($product["name"]); ?>"
                                            >

                                        <?php else: ?>

                                            <span>📦</span>

                                        <?php endif; ?>

                                    </div>


                                    <div>

                                        <strong>
                                            <?php echo htmlspecialchars($product["name"]); ?>
                                        </strong>

                                        <small>

                                            <?php

                                            $description = $product["description"] ?? "";

                                            echo htmlspecialchars(
                                                strlen($description) > 45
                                                    ? substr($description, 0, 45) . "..."
                                                    : $description
                                            );

                                            ?>

                                        </small>

                                    </div>


                                </div>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $product["category_name"] ?? "Uncategorized"
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $product["seller_name"] ?? "ShopSphere"
                                );
                                ?>

                            </td>


                            <td>

                                Rs.
                                <?php echo number_format($product["price"], 2); ?>

                            </td>


                            <td>

                                <?php if ($product["stock"] <= 2): ?>

                                    <span class="stock-critical">
                                        <?php echo $product["stock"]; ?>
                                    </span>

                                <?php elseif ($product["stock"] <= 5): ?>

                                    <span class="stock-low">
                                        <?php echo $product["stock"]; ?>
                                    </span>

                                <?php else: ?>

                                    <span class="stock-good">
                                        <?php echo $product["stock"]; ?>
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <span
                                    class="status-badge
                                    <?php echo $product["status"] === "active"
                                        ? "status-delivered"
                                        : "status-cancelled"; ?>"
                                >

                                    <?php echo ucfirst($product["status"]); ?>

                                </span>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime($product["created_at"])
                                );
                                ?>

                            </td>


                            <td>

                                <div class="table-actions">


                                    <a
                                        href="products.php?edit=<?php echo $product["product_id"]; ?>"
                                        class="edit-action"
                                    >
                                        Edit
                                    </a>


                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this product?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        >

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?php echo $product["product_id"]; ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="delete-action"
                                        >
                                            Delete
                                        </button>

                                    </form>


                                </div>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="9"
                            class="empty-table"
                        >
                            No products found.
                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>


        </div>


    </section>


</main>


<?php include "includes/footer.php"; ?>

</div>


<div
    class="product-modal"
    id="productModal"
>

    <div class="product-modal-overlay" onclick="closeProductForm()"></div>


    <div class="product-modal-box">


        <div class="product-modal-header">

            <div>

                <span class="admin-breadcrumb">
                    Product Management
                </span>

                <h2>
                    <?php echo $edit_product ? "Edit Product" : "Add Product"; ?>
                </h2>

            </div>


            <button
                type="button"
                onclick="closeProductForm()"
                class="modal-close"
            >
                ×
            </button>

        </div>


        <form
            method="POST"
            enctype="multipart/form-data"
            class="product-form"
        >


            <input
                type="hidden"
                name="action"
                value="<?php echo $edit_product ? "update" : "add"; ?>"
            >


            <?php if ($edit_product): ?>

                <input
                    type="hidden"
                    name="product_id"
                    value="<?php echo $edit_product["product_id"]; ?>"
                >

                <input
                    type="hidden"
                    name="existing_image"
                    value="<?php echo htmlspecialchars($edit_product["image"] ?? ""); ?>"
                >

            <?php endif; ?>


            <div class="form-grid">


                <div class="form-group form-full">

                    <label>
                        Product Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        required
                        value="<?php echo htmlspecialchars($edit_product["name"] ?? ""); ?>"
                        placeholder="Enter product name"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Category
                    </label>

                    <select
                        name="category_id"
                        required
                    >

                        <option value="">
                            Select Category
                        </option>

                        <?php

                        $form_categories = $conn->query("
                            SELECT category_id, name
                            FROM categories
                            ORDER BY name ASC
                        ");

                        ?>

                        <?php while ($category = $form_categories->fetch_assoc()): ?>

                            <option
                                value="<?php echo $category["category_id"]; ?>"
                                <?php echo isset($edit_product["category_id"]) &&
                                    $edit_product["category_id"] == $category["category_id"]
                                    ? "selected"
                                    : ""; ?>
                            >
                                <?php echo htmlspecialchars($category["name"]); ?>
                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        Seller
                    </label>

                    <select name="seller_id">

                        <option value="">
                            ShopSphere
                        </option>

                        <?php

                        $form_sellers = $conn->query("
                            SELECT user_id, name
                            FROM users
                            WHERE role = 'seller'
                            ORDER BY name ASC
                        ");

                        ?>

                        <?php while ($seller = $form_sellers->fetch_assoc()): ?>

                            <option
                                value="<?php echo $seller["user_id"]; ?>"
                                <?php echo isset($edit_product["seller_id"]) &&
                                    $edit_product["seller_id"] == $seller["user_id"]
                                    ? "selected"
                                    : ""; ?>
                            >
                                <?php echo htmlspecialchars($seller["name"]); ?>
                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        Price
                    </label>

                    <input
                        type="number"
                        name="price"
                        step="0.01"
                        min="0"
                        required
                        value="<?php echo htmlspecialchars($edit_product["price"] ?? ""); ?>"
                        placeholder="0.00"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Stock
                    </label>

                    <input
                        type="number"
                        name="stock"
                        min="0"
                        required
                        value="<?php echo htmlspecialchars($edit_product["stock"] ?? "0"); ?>"
                        placeholder="0"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Status
                    </label>

                    <select name="status">

                        <option
                            value="active"
                            <?php echo ($edit_product["status"] ?? "active") === "active"
                                ? "selected"
                                : ""; ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?php echo ($edit_product["status"] ?? "") === "inactive"
                                ? "selected"
                                : ""; ?>
                        >
                            Inactive
                        </option>

                    </select>

                </div>


                <div class="form-group form-full">

                    <label>
                        Product Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <?php if (!empty($edit_product["image"])): ?>

                        <small class="current-image">
                            Current image:
                            <?php echo htmlspecialchars($edit_product["image"]); ?>
                        </small>

                    <?php endif; ?>

                </div>


                <div class="form-group form-full">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="5"
                        placeholder="Enter product description"
                    ><?php echo htmlspecialchars($edit_product["description"] ?? ""); ?></textarea>

                </div>


            </div>


            <div class="product-form-actions">

                <button
                    type="button"
                    class="modal-cancel"
                    onclick="closeProductForm()"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="admin-primary-button"
                >
                    <?php echo $edit_product ? "Update Product" : "Add Product"; ?>
                </button>

            </div>


        </form>


    </div>

</div>


<script src="../assets/js/admin.js"></script>


<script>

function openProductForm() {
    document.getElementById("productModal").classList.add("show");
}

function closeProductForm() {
    document.getElementById("productModal").classList.remove("show");

    <?php if ($edit_product): ?>

        window.location.href = "products.php";

    <?php endif; ?>
}

<?php if ($edit_product): ?>

    openProductForm();

<?php endif; ?>

</script>

</body>

</html>