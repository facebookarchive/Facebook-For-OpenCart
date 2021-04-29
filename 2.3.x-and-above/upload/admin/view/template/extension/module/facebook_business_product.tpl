<!-- Copyright 2017-present, Facebook, Inc. -->
<!-- All rights reserved. -->

<!-- This source code is licensed under the license found in the -->
<!-- LICENSE file in the root directory of this source tree. -->
<div class="tab-pane" id="tab-facebook">
  <div class="alert alert-info"><i class="fa fa-exclamation-circle"></i> <?php echo $text_additional_fields_info; ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label" for="input-facebook-google-product-category"><?php echo $entry_facebook_google_product_category; ?></label>
    <div class="col-sm-10">
      <select name="facebook_google_product_category"  id="input-facebook-google-product-category" class="form-control">
        <option value="0"><?php echo $text_please_select; ?></option>
        <?php if ($google_product_categories && !$error_google_product_category) { ?>
        <?php foreach ($google_product_categories as $id => $name) { ?>
        <option value="<?php echo $id; ?>"<?php echo $facebook_google_product_category == $id ? ' selected="selected"' : ''; ?>><?php echo $name; ?></option>
        <?php } ?>
        <?php } else { ?>
        <div class="text-danger"><?php echo $error_google_product_category; ?></div>
        <?php } ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label" for="input-facebook-condition"><?php echo $entry_facebook_condition; ?></label>
    <div class="col-sm-10">
      <select name="facebook_condition"  id="input-facebook-condition" class="form-control">
        <option value=""><?php echo $text_please_select; ?></option>
        <option value="<?php echo $text_condition_new; ?>"<?php echo $facebook_condition == $text_condition_new ? ' selected="selected"' : ''; ?>><?php echo $text_condition_new; ?></option>
        <option value="<?php echo $text_condition_refurbished; ?>"<?php echo $facebook_condition == $text_condition_refurbished ? ' selected="selected"' : ''; ?>><?php echo $text_condition_refurbished; ?></option>
        <option value="<?php echo $text_condition_used; ?>"<?php echo $facebook_condition == $text_condition_used ? ' selected="selected"' : ''; ?>><?php echo $text_condition_used; ?></option>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label" for="input-facebook-age-group"><?php echo $entry_facebook_age_group; ?></label>
    <div class="col-sm-10">
      <select name="facebook_age_group"  id="input-facebook-age-group" class="form-control">
        <option value=""><?php echo $text_please_select; ?></option>
        <option value="<?php echo $text_age_group_all_ages; ?>"<?php echo $facebook_age_group == $text_age_group_all_ages ? ' selected="selected"' : ''; ?>><?php echo $text_age_group_all_ages; ?></option>
        <option value="<?php echo $text_age_group_adult; ?>"<?php echo $facebook_age_group == $text_age_group_adult ? ' selected="selected"' : ''; ?>><?php echo $text_age_group_adult; ?></option>
        <option value="<?php echo $text_age_group_teen; ?>"<?php echo $facebook_age_group == $text_age_group_teen ? ' selected="selected"' : ''; ?>><?php echo $text_age_group_teen; ?></option>
        <option value="<?php echo $text_age_group_kids; ?>"<?php echo $facebook_age_group == $text_age_group_kids ? ' selected="selected"' : ''; ?>><?php echo $text_age_group_kids; ?></option>
        <option value="<?php echo $text_age_group_toddler; ?>"<?php echo $facebook_age_group == $text_age_group_toddler ? ' selected="selected"' : ''; ?>><?php echo $text_age_group_toddler; ?></option>
        <option value="<?php echo $text_age_group_infant; ?>"<?php echo $facebook_age_group == $text_age_group_infant ? ' selected="selected"' : ''; ?>><?php echo $text_age_group_infant; ?></option>
        <option value="<?php echo $text_age_group_newborn; ?>"<?php echo $facebook_age_group == $text_age_group_newborn ? ' selected="selected"' : ''; ?>><?php echo $text_age_group_newborn; ?></option>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label" for="input-facebook-gender"><?php echo $entry_facebook_gender; ?></label>
    <div class="col-sm-10">
      <select name="facebook_gender"  id="input-facebook-gender" class="form-control">
        <option value=""><?php echo $text_please_select; ?></option>
        <option value="<?php echo $text_gender_female; ?>"<?php echo $facebook_gender == $text_gender_female ? ' selected="selected"' : ''; ?>><?php echo $text_gender_female; ?></option>
        <option value="<?php echo $text_gender_male; ?>"<?php echo $facebook_gender == $text_gender_male ? ' selected="selected"' : ''; ?>><?php echo $text_gender_male; ?></option>
        <option value="<?php echo $text_gender_unisex; ?>"<?php echo $facebook_gender == $text_gender_unisex ? ' selected="selected"' : ''; ?>><?php echo $text_gender_unisex; ?></option>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label" for="input-facebook-material"><?php echo $entry_facebook_material; ?></label>
    <div class="col-sm-10">
      <select name="facebook_material"  id="input-facebook-material" class="form-control">
        <option value=""><?php echo $text_please_select; ?></option>
        <option value="<?php echo $text_material_cotton; ?>"<?php echo $facebook_material == $text_material_cotton ? ' selected="selected"' : ''; ?>><?php echo $text_material_cotton; ?></option>
        <option value="<?php echo $text_material_denim; ?>"<?php echo $facebook_material == $text_material_denim ? ' selected="selected"' : ''; ?>><?php echo $text_material_denim; ?></option>
        <option value="<?php echo $text_material_leather; ?>"<?php echo $facebook_material == $text_material_leather ? ' selected="selected"' : ''; ?>><?php echo $text_material_leather; ?></option>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label" for="input-facebook-color"><?php echo $entry_facebook_color; ?></label>
    <div class="col-sm-10">
      <input type="text" name="facebook_color" value="<?php echo $facebook_color; ?>" placeholder="<?php echo $entry_facebook_color; ?>" id="input-facebook-color" class="form-control" />
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label" for="input-facebook-pattern"><?php echo $entry_facebook_pattern; ?></label>
    <div class="col-sm-10">
      <input type="text" name="facebook_pattern" value="<?php echo $facebook_pattern; ?>" placeholder="<?php echo $entry_facebook_pattern; ?>" id="input-facebook-pattern" class="form-control" />
    </div>
  </div>
</div>