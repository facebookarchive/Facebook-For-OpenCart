<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class ModelExtensionFacebookProduct extends Model {
  public function addFacebookProduct(
    $product_id,
    $facebook_product_id,
    $facebook_product_group_id) {
    $this->db->query("INSERT INTO " . DB_PREFIX . "facebook_product " .
      "SET product_id = " . (int)$product_id . "," .
      "facebook_product_id = '" . $facebook_product_id . "'," .
      "facebook_product_group_id = '" . $facebook_product_group_id . "'");
  }

  public function deleteAllFacebookProducts() {
    $this->db->query("DELETE FROM " . DB_PREFIX . "facebook_product ");
  }

  public function getFacebookProductIdAndProductGroupId($product_id) {
    $query = $this->db->query("SELECT facebook_product_id, " .
      "facebook_product_group_id " .
      "FROM " . DB_PREFIX . "facebook_product " .
      "WHERE product_id = " . (int)$product_id);
    return (isset($query->row['facebook_product_id'])
      && $query->row['facebook_product_group_id'])
      ? array(
        $query->row['facebook_product_id'],
        $query->row['facebook_product_group_id'])
      : array('', '');
  }

  public function deleteFacebookProduct($product_id) {
    $this->db->query("DELETE FROM " . DB_PREFIX . "facebook_product " .
      "WHERE product_id = " . (int)$product_id);
  }

  public function getFacebookProductAvailabiltyStatus($product_ids) {
    $sql = "SELECT p.product_id, fbp.facebook_product_id, " .
      "p.quantity, p.subtract, p.stock_status_id " .
      "FROM " . DB_PREFIX . "product p " .
      "LEFT JOIN " .DB_PREFIX . "facebook_product fbp " .
      "ON fbp.product_id = p.product_id " .
      "WHERE p.product_id IN (" . implode(",", $product_ids) . ")";
    return $this->db->query($sql)->rows;
  }

  public function updateFacebookProduct(
    $product_id,
    $facebook_product_id,
    $facebook_product_group_id) {
    $this->db->query("UPDATE " . DB_PREFIX . "facebook_product " .
      "SET facebook_product_id = '" . $facebook_product_id . "'," .
      "facebook_product_group_id = '" . $facebook_product_group_id . "' " .
      "WHERE product_id = " . (int)$product_id);
  }

  // this function is a direct lifting from admin/model/catalog/product.php
  // except that the SQL query is joining other tables to obtain
  // brand, category, facebook_product_id and facebook_product_group_id
  // the rational to duplicate this method into this external class
  // instead of modifying the existing method which may lead to
  // breakage with other 3rd party plugins
  public function getProducts($data = array()) {
    $sql = "SELECT p.*, " .
      "pd.*, " .
      "m.name AS manufacturer_name, " .
      "fbp.facebook_product_id, " .
      "fbp.facebook_product_group_id, " .
      "ptc.category_name " .
      "FROM " . DB_PREFIX . "product p " .
      "LEFT JOIN " . DB_PREFIX . "product_description pd " .
        " ON (p.product_id = pd.product_id) " .
      "LEFT JOIN " . DB_PREFIX . "manufacturer m " .
        " ON (p.manufacturer_id = m.manufacturer_id) " .
      "LEFT JOIN " . DB_PREFIX . "facebook_product fbp " .
        " ON (p.product_id = fbp.product_id) " .
      // Retrieving category name for each product.
      // OpenCart allows each product to tag to multiple categories
      // and this relationship is stored in product_to_category.
      // So the approach is to retrieve the category with max id
      // and use it for the product.
      // The category name is also stored separately in category_description
      "LEFT JOIN " .
        "(SELECT ptc.product_id, ptc.category_id, cd.name AS category_name " .
          "FROM (SELECT product_id, MAX(category_id) AS category_id " .
            "FROM " . DB_PREFIX . "product_to_category " .
            "GROUP BY product_id) AS ptc " .
          "LEFT JOIN " . DB_PREFIX . "category_description cd " .
            "ON (ptc.category_id = cd.category_id)) ptc " .
        "ON (p.product_id = ptc.product_id) " .
      "WHERE pd.language_id = '" .
        (int)$this->config->get('config_language_id') . "'";

    if (!empty($data['filter_name'])) {
      $sql .= " AND pd.name LIKE '" .
        $this->db->escape($data['filter_name']) . "%'";
    }

    if (!empty($data['filter_model'])) {
      $sql .= " AND p.model LIKE '" .
        $this->db->escape($data['filter_model']) . "%'";
    }

    if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
      $sql .= " AND p.price LIKE '" .
        $this->db->escape($data['filter_price']) . "%'";
    }

    if (isset($data['filter_quantity'])
      && !is_null($data['filter_quantity'])) {
      $sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
    }

    if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
      $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
    }

    if (isset($data['filter_image']) && !is_null($data['filter_image'])) {
      if ($data['filter_image'] == 1) {
        $sql .= " AND (p.image IS NOT NULL AND p.image <> '' " .
          "AND p.image <> 'no_image.png')";
      } else {
        $sql .= " AND (p.image IS NULL OR p.image = '' " .
          "OR p.image = 'no_image.png')";
      }
    }

    $sql .= " GROUP BY p.product_id";

    $sort_data = array(
      'pd.name',
      'p.model',
      'p.price',
      'p.quantity',
      'p.status',
      'p.sort_order'
    );

    if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
      $sql .= " ORDER BY " . $data['sort'];
    } else {
      $sql .= " ORDER BY pd.name";
    }

    if (isset($data['order']) && ($data['order'] == 'DESC')) {
      $sql .= " DESC";
    } else {
      $sql .= " ASC";
    }

    if (isset($data['start']) || isset($data['limit'])) {
      if ($data['start'] < 0) {
        $data['start'] = 0;
      }

      if ($data['limit'] < 1) {
        $data['limit'] = 20;
      }

      $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
    }

    $query = $this->db->query($sql);

    return $query->rows;
  }

  // similar as getProducts,
  // this function is a direct lifting from admin/model/catalog/product.php
  public function getProduct($product_id) {
    // this query change is similar to getProducts()
    // we are no longer left join the ua_link table as
    // 1. we are not using the keyword from ua_link in our plugin
    // 2. OpenCart v3 renamed this table to seo_link
    $query = $this->db->query(
      "SELECT " .
        "p.*, " .
        "pd.*, " .
        "m.name AS manufacturer_name, " .
        "fbp.facebook_product_id, " .
        "ptc.category_name " .
      "FROM " . DB_PREFIX . "product p " .
      "LEFT JOIN " . DB_PREFIX . "product_description pd " .
        "ON (p.product_id = pd.product_id) " .
      "LEFT JOIN " . DB_PREFIX . "manufacturer m " .
        "ON (p.manufacturer_id = m.manufacturer_id) " .
      "LEFT JOIN " . DB_PREFIX . "facebook_product fbp " .
        "ON (p.product_id = fbp.product_id) " .
      "LEFT JOIN " .
        "(SELECT ptc.product_id, " .
          "ptc.category_id, " .
          "cd.name AS category_name " .
          "FROM (SELECT product_id, MAX(category_id) AS category_id " .
            "FROM " . DB_PREFIX . "product_to_category " .
            "GROUP BY product_id) AS ptc " .
          "LEFT JOIN " . DB_PREFIX . "category_description cd " .
            "ON (ptc.category_id = cd.category_id)) ptc " .
        "ON (p.product_id = ptc.product_id) " .
      "WHERE p.product_id = '" . (int)$product_id . "' " .
        "AND pd.language_id = '" .
          (int)$this->config->get('config_language_id') . "'");

    return $query->row;
  }

}
