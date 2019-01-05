<select class="form-control" multiple="multiple" name="subscribers[]" id="subscribers" data-error-container="#users_error_container">
    <?php echo $this->users; ?>
</select>
<script type="text/javascript">
    $('#subscribers').multiSelect();
</script>
