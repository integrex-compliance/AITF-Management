<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "AI Review Rules";
    $addAiRulesClass = 'active';
    include_once './header.php';
    include_once './dbConnect.php';
    include_once './utility.php';
    include_once './include/ai_document_review_lib.php';
    date_default_timezone_set('Asia/Kolkata');

    if (!(Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff())) {
        header("Location: homePage.php");
        exit;
    }

    aitfAiEnsureSchema($db);
    $message = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        try {
            if ($action === 'save_rule') {
                $id = isset($_POST['rule_id']) ? (int) $_POST['rule_id'] : 0;
                $ruleCode = strtoupper(trim((string) $_POST['rule_code']));
                $title = trim((string) $_POST['title']);
                $category = trim((string) $_POST['category']);
                $ruleText = trim((string) $_POST['rule_text']);
                $expectedValue = trim((string) $_POST['expected_value']);
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                if ($ruleCode === '' || $title === '' || $ruleText === '') {
                    throw new Exception('Rule code, title, and rule text are required.');
                }

                if ($id > 0) {
                    $stmt = $db->prepare("UPDATE ai_review_rules SET rule_code = :rule_code, title = :title, category = :category, rule_text = :rule_text, expected_value = :expected_value, is_active = :is_active, updated_by = :updated_by WHERE id = :id");
                    $stmt->execute(array(
                        ':rule_code' => $ruleCode,
                        ':title' => $title,
                        ':category' => $category,
                        ':rule_text' => $ruleText,
                        ':expected_value' => $expectedValue,
                        ':is_active' => $isActive,
                        ':updated_by' => isset($_SESSION['username']) ? $_SESSION['username'] : '',
                        ':id' => $id
                    ));
                    $message = 'AI rule updated.';
                } else {
                    $stmt = $db->prepare("INSERT INTO ai_review_rules (rule_code, title, category, rule_text, expected_value, is_active, created_by, updated_by) VALUES (:rule_code, :title, :category, :rule_text, :expected_value, :is_active, :created_by, :updated_by)");
                    $stmt->execute(array(
                        ':rule_code' => $ruleCode,
                        ':title' => $title,
                        ':category' => $category,
                        ':rule_text' => $ruleText,
                        ':expected_value' => $expectedValue,
                        ':is_active' => $isActive,
                        ':created_by' => isset($_SESSION['username']) ? $_SESSION['username'] : '',
                        ':updated_by' => isset($_SESSION['username']) ? $_SESSION['username'] : ''
                    ));
                    $message = 'AI rule added.';
                }
            } elseif ($action === 'toggle_rule') {
                $id = (int) $_POST['rule_id'];
                $active = (int) $_POST['active'];
                $stmt = $db->prepare("UPDATE ai_review_rules SET is_active = :active, updated_by = :updated_by WHERE id = :id");
                $stmt->execute(array(
                    ':active' => $active,
                    ':updated_by' => isset($_SESSION['username']) ? $_SESSION['username'] : '',
                    ':id' => $id
                ));
                $message = $active ? 'AI rule activated.' : 'AI rule deactivated.';
            }
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }
    }

    $editRule = null;
    if (isset($_GET['edit'])) {
        $stmt = $db->prepare("SELECT * FROM ai_review_rules WHERE id = :id");
        $stmt->execute(array(':id' => (int) $_GET['edit']));
        $editRule = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $stmt = $db->prepare("SELECT * FROM ai_review_rules ORDER BY is_active DESC, category ASC, rule_code ASC, id DESC");
    $stmt->execute();
    $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container" style="margin-top: 70px;">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="row" style="">
                            <div class="col-xs-12 text-center btn qms_complete_btn">
                                <span><i class="fa fa-magic"> AI Powered by AITF Rules</i></span>
                            </div>
                        </div>

                        <?php if ($message !== '') { ?>
                            <div class="alert alert-success" style="margin-top: 15px;"><?php echo htmlspecialchars($message); ?></div>
                        <?php } ?>
                        <?php if ($error !== '') { ?>
                            <div class="alert alert-danger" style="margin-top: 15px;"><?php echo htmlspecialchars($error); ?></div>
                        <?php } ?>

                        <div class="well" style="margin-top: 15px;">
                            <form method="post" action="aiReviewRules.php">
                                <input type="hidden" name="action" value="save_rule">
                                <input type="hidden" name="rule_id" value="<?php echo $editRule ? (int) $editRule['id'] : 0; ?>">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <label>Rule Code</label>
                                        <input type="text" class="form-control" name="rule_code" maxlength="80" required value="<?php echo htmlspecialchars($editRule ? $editRule['rule_code'] : ''); ?>" placeholder="e.g. ISO-DATE-001">
                                    </div>
                                    <div class="col-sm-5">
                                        <label>Title</label>
                                        <input type="text" class="form-control" name="title" maxlength="255" required value="<?php echo htmlspecialchars($editRule ? $editRule['title'] : ''); ?>">
                                    </div>
                                    <div class="col-sm-3">
                                        <label>Category</label>
                                        <input type="text" class="form-control" name="category" maxlength="120" value="<?php echo htmlspecialchars($editRule ? $editRule['category'] : ''); ?>" placeholder="Dates, Scope, Mandays">
                                    </div>
                                    <div class="col-sm-1">
                                        <label>Active</label><br>
                                        <input type="checkbox" name="is_active" value="1" <?php echo (!$editRule || (int) $editRule['is_active'] === 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                                <div class="row" style="margin-top: 12px;">
                                    <div class="col-sm-7">
                                        <label>Rule Text</label>
                                        <textarea class="form-control" name="rule_text" rows="4" required><?php echo htmlspecialchars($editRule ? $editRule['rule_text'] : ''); ?></textarea>
                                    </div>
                                    <div class="col-sm-5">
                                        <label>Expected Value / Evidence Source</label>
                                        <textarea class="form-control" name="expected_value" rows="4" placeholder="Leave blank if the AI must use document evidence and mark unknowns as Manual verification required."><?php echo htmlspecialchars($editRule ? $editRule['expected_value'] : ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="row" style="margin-top: 12px;">
                                    <div class="col-sm-12">
                                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?php echo $editRule ? 'Update Rule' : 'Add Rule'; ?></button>
                                        <?php if ($editRule) { ?>
                                            <a href="aiReviewRules.php" class="btn btn-default">Cancel Edit</a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 12%;">Code</th>
                                        <th style="width: 18%;">Title</th>
                                        <th style="width: 12%;">Category</th>
                                        <th>Rule</th>
                                        <th style="width: 12%;">Expected</th>
                                        <th style="width: 9%;">Status</th>
                                        <th style="width: 12%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($rules)) { ?>
                                        <tr><td colspan="7" class="text-center">No AI review rules added yet.</td></tr>
                                    <?php } ?>
                                    <?php foreach ($rules as $rule) { ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($rule['rule_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($rule['title']); ?></td>
                                            <td><?php echo htmlspecialchars($rule['category']); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($rule['rule_text'])); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($rule['expected_value'])); ?></td>
                                            <td>
                                                <?php if ((int) $rule['is_active'] === 1) { ?>
                                                    <span class="label label-success">Active</span>
                                                <?php } else { ?>
                                                    <span class="label label-default">Inactive</span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <a class="btn btn-xs btn-primary" href="aiReviewRules.php?edit=<?php echo (int) $rule['id']; ?>"><i class="fa fa-pencil"></i> Edit</a>
                                                <form method="post" action="aiReviewRules.php" style="display: inline;">
                                                    <input type="hidden" name="action" value="toggle_rule">
                                                    <input type="hidden" name="rule_id" value="<?php echo (int) $rule['id']; ?>">
                                                    <input type="hidden" name="active" value="<?php echo (int) $rule['is_active'] === 1 ? 0 : 1; ?>">
                                                    <button type="submit" class="btn btn-xs <?php echo (int) $rule['is_active'] === 1 ? 'btn-warning' : 'btn-success'; ?>">
                                                        <?php echo (int) $rule['is_active'] === 1 ? 'Deactivate' : 'Activate'; ?>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
