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

        $name = trim($_POST["name"]);
        $description = trim($_POST["description"]);
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
            INSERT INTO categories
            (name, description, image)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param(
            "sss",
            $name,
            $description,
            $image
        );

        if ($stmt->execute()) {
            $message = "Category added successfully.";
            $message_type = "success";
        } else {
            $message = "Unable to add category. Category name may already exist.";
            $message_type = "error";
        }
    }


    if ($action === "update") {

        $category_id = intval($_POST["category_id"]);
        $name = trim($_POST["name"]);
        $description = trim($_POST["description"]);
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
            UPDATE categories
            SET
                name = ?,
                description = ?,
                image = ?
            WHERE category_id = ?
        ");

        $stmt->bind_param(
            "sssi",
            $name,
            $description,
            $image,
            $category_id
        );

        if ($stmt->execute()) {
            $message = "Category updated successfully.";
            $message_type = "success";
        } else {
            $message = "Unable to update category.";
            $message_type = "error";
        }
    }


    if ($action === "delete") {

        $category_id = intval($_POST["category_id"]);

        $check = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM products
            WHERE category_id = ?
        ");

        $check->bind_param("i", $category_id);
        $check->execute();

        $check_result = $check->get_result();
        $product_count = $check_result->fetch_assoc()["total"];

        if ($product_count > 0) {

            $message = "This category cannot be deleted because it contains products.";
            $message_type = "error";

        } else {

            $stmt = $conn->prepare("
                DELETE FROM categories
                WHERE category_id = ?
            ");

            $stmt->bind_param("i", $category_id);

            if ($stmt->execute()) {
                $message = "Category deleted successfully.";
                $message_type = "success";
            } else {
                $message = "Unable to delete category.";
                $message_type = "error";
            }
        }
    }
}


$search = trim($_GET["search"] ?? "");


$query = "
    SELECT
        c.category_id,
        c.name,
        c.description,
        c.image,
        c.created_at,
        COUNT(p.product_id) AS product_count
    FROM categories c
    LEFT JOIN products p
        ON c.category_id = p.category_id
";


$params = [];
$types = "";


if ($search !== "") {

    $query .= "
        WHERE
            c.name LIKE ?
            OR c.description LIKE ?
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;

    $types = "ss";
}


$query .= "
    GROUP BY
        c.category_id,
        c.name,
        c.description,
        c.image,
        c.created_at
    ORDER BY c.category_id DESC
";


$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$categories = $stmt->get_result();


$edit_category = null;

if (isset($_GET["edit"])) {

    $edit_id = intval($_GET["edit"]);

    $stmt = $conn->prepare("
        SELECT *
        FROM categories
        WHERE category_id = ?
    ");

    $stmt->bind_param("i", $edit_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $edit_category = $result->fetch_assoc();
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

    <title>Categories | ShopSphere Admin</title>

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
                Administration / Categories
            </span>

            <h1>Categories</h1>

            <p>
                Organize products into manageable categories.
            </p>

        </div>

        <button
            type="button"
            class="admin-primary-button"
            onclick="openCategoryForm()"
        >
            + Add Category
        </button>

    </div>


    <?php if ($message !== ""): ?>

        <div class="admin-alert <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <section class="admin-panel">


        <div class="product-toolbar">


            <form
                method="GET"
                class="product-filter-form"
            >

                <div class="admin-filter-search">

                    <span>🔍</span>

                    <input
                        type="text"
                        name="search"
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search categories..."
                    >

                </div>


                <button
                    type="submit"
                    class="filter-button"
                >
                    Search
                </button>


                <a
                    href="categories.php"
                    class="clear-filter"
                >
                    Clear
                </a>

            </form>


            <div class="product-count">

                <?php echo $categories->num_rows; ?> categories

            </div>


        </div>


        <div class="admin-table-wrapper">


            <table class="admin-table category-table">


                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Category</th>

                        <th>Description</th>

                        <th>Products</th>

                        <th>Created</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>


                <?php if ($categories->num_rows > 0): ?>


                    <?php while ($category = $categories->fetch_assoc()): ?>


                        <tr>


                            <td>

                                <strong>
                                    #<?php echo $category["category_id"]; ?>
                                </strong>

                            </td>


                            <td>

                                <div class="category-table-info">


                                    <div class="category-table-image">

                                        <?php if (!empty($category["image"])): ?>

                                            <img
                                                src="../assets/images/<?php echo htmlspecialchars($category["image"]); ?>"
                                                alt="<?php echo htmlspecialchars($category["name"]); ?>"
                                            >

                                        <?php else: ?>

                                            <span>📂</span>

                                        <?php endif; ?>

                                    </div>


                                    <strong>
                                        <?php echo htmlspecialchars($category["name"]); ?>
                                    </strong>


                                </div>

                            </td>


                            <td>

                                <span class="category-description">

                                    <?php

                                    $description = $category["description"] ?? "";

                                    if ($description === "") {

                                        echo "No description";

                                    } elseif (strlen($description) > 60) {

                                        echo htmlspecialchars(
                                            substr($description, 0, 60) . "..."
                                        );

                                    } else {

                                        echo htmlspecialchars($description);

                                    }

                                    ?>

                                </span>

                            </td>


                            <td>

                                <span class="product-count-badge">

                                    <?php echo $category["product_count"]; ?>

                                </span>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime($category["created_at"])
                                );
                                ?>

                            </td>


                            <td>

                                <div class="table-actions">


                                    <a
                                        href="categories.php?edit=<?php echo $category["category_id"]; ?>"
                                        class="edit-action"
                                    >
                                        Edit
                                    </a>


                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this category?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        >

                                        <input
                                            type="hidden"
                                            name="category_id"
                                            value="<?php echo $category["category_id"]; ?>"
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
                            colspan="6"
                            class="empty-table"
                        >
                            No categories found.
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
    id="categoryModal"
>

    <div
        class="product-modal-overlay"
        onclick="closeCategoryForm()"
    ></div>


    <div class="product-modal-box">


        <div class="product-modal-header">

            <div>

                <span class="admin-breadcrumb">
                    Category Management
                </span>

                <h2>
                    <?php echo $edit_category ? "Edit Category" : "Add Category"; ?>
                </h2>

            </div>


            <button
                type="button"
                onclick="closeCategoryForm()"
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
                value="<?php echo $edit_category ? "update" : "add"; ?>"
            >


            <?php if ($edit_category): ?>

                <input
                    type="hidden"
                    name="category_id"
                    value="<?php echo $edit_category["category_id"]; ?>"
                >

                <input
                    type="hidden"
                    name="existing_image"
                    value="<?php echo htmlspecialchars($edit_category["image"] ?? ""); ?>"
                >

            <?php endif; ?>


            <div class="form-grid">


                <div class="form-group form-full">

                    <label>
                        Category Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        required
                        value="<?php echo htmlspecialchars($edit_category["name"] ?? ""); ?>"
                        placeholder="Enter category name"
                    >

                </div>


                <div class="form-group form-full">

                    <label>
                        Category Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <?php if (!empty($edit_category["image"])): ?>

                        <small class="current-image">

                            Current image:
                            <?php echo htmlspecialchars($edit_category["image"]); ?>

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
                        placeholder="Enter category description"
                    ><?php echo htmlspecialchars($edit_category["description"] ?? ""); ?></textarea>

                </div>


            </div>


            <div class="product-form-actions">

                <button
                    type="button"
                    class="modal-cancel"
                    onclick="closeCategoryForm()"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="admin-primary-button"
                >

                    <?php echo $edit_category
                        ? "Update Category"
                        : "Add Category"; ?>

                </button>

            </div>


        </form>


    </div>

</div>


<script src="../assets/js/admin.js"></script>


<script>

function openCategoryForm() {
    document.getElementById("categoryModal").classList.add("show");
}

function closeCategoryForm() {

    document.getElementById("categoryModal").classList.remove("show");

    <?php if ($edit_category): ?>

        window.location.href = "categories.php";

    <?php endif; ?>

}

<?php if ($edit_category): ?>

openCategoryForm();

<?php endif; ?>

</script>

</body>

</html>