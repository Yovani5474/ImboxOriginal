<!-- Top Bar -->
<div class="top-bar">
    <h1 class="page-title"><?php echo $page_title ?? 'Dashboard'; ?></h1>
    <div class="user-info">
        <div class="user-avatar">
            <?php echo strtoupper(substr(getUserName(), 0, 1)); ?>
        </div>
        <span class="user-name"><?php echo getUserName(); ?></span>
    </div>
</div>
