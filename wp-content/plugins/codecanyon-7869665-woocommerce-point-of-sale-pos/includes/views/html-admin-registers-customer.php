<?php 
if (!empty($user_to_add)) {
    ?>
    <tr data-customer_id="<?php echo $user_to_add; ?>" class="item <?php if (!empty($class)) echo $class; ?>">
        <td class="name">
            <?php if (!$user_to_add) { ?>
                <?php echo $username; ?>
            <?php } else { ?>
                <a href="user-edit.php?user_id=<?php echo $user_to_add; ?>" target="_blank"><?php echo get_user_meta($user_to_add, 'first_name', true) . ' ' . get_user_meta($user_to_add, 'last_name', true); ?></a>
            <?php } ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr($user_to_add); ?>" />
        </td>
        
        <td class="remove_customer">
            <a href="#" class="remove_customer_row tips" data-tip="<?php _e('Remove', 'wc_point_of_sale'); ?>"></a>
        </td>
    </tr>
    <?php } else {
    ?>
    <!-- For place Order from Guest Account -->
    <tr data-customer_id="<?php echo $user_to_add; ?>" class="item <?php if (!empty($class)) echo $class; ?>">
        <td class="name" >Guest</td>
        <td class="remove_customer">
            <a href="#" class="remove_customer_row tips" data-tip="<?php _e('Remove', 'wc_point_of_sale'); ?>"></a>
        </td>
    </tr> 
<?php }
?>