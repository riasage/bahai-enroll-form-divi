<?php
$texts = isset($texts) ? $texts : BEFD_Form::texts();
$lang = isset($_GET['lang']) && $_GET['lang']==='ja' ? 'ja' : 'en';
?>
<div class="befd-form-wrap et_pb_module et_pb_blurb">
  <div class="befd-header">
    <h1 class="befd-title"><?php echo esc_html( $texts['title_' . $lang] ); ?></h1>
    <p class="befd-preamble"><?php echo esc_html( $texts['preamble_' . $lang] ); ?></p>
    <div class="befd-lang-toggle">
      <a href="<?php echo esc_url( add_query_arg('lang','en') ); ?>" class="befd-lang <?php echo $lang==='en'?'active':''; ?>">English</a> |
      <a href="<?php echo esc_url( add_query_arg('lang','ja') ); ?>" class="befd-lang <?php echo $lang==='ja'?'active':''; ?>">日本語</a>
    </div>
  </div>

  <form class="befd-form" method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
    <input type="hidden" name="action" value="befd_submit" />
    <input type="hidden" name="lang" value="<?php echo esc_attr($lang); ?>" />
    <?php wp_nonce_field( 'befd_submit', 'befd_nonce' ); ?>
    <div class="befd-row">
      <label><?php echo esc_html($texts['name_' . $lang]); ?></label>
      <input type="text" name="name" placeholder="<?php echo esc_attr($lang==='ja'?'氏名を入力してください':'Enter Your Full Name Here'); ?>" required />
    </div>
    <div class="befd-row">
      <label><?php echo esc_html($texts['address_' . $lang]); ?></label>
      <textarea name="address" placeholder="<?php echo esc_attr($lang==='ja'?'住所を入力してください':'Enter Your Full Address here'); ?>" rows="3" required></textarea>
    </div>
    <div class="befd-grid">
      <div class="befd-col">
        <label><?php echo esc_html($texts['dob_' . $lang]); ?></label>
        <input type="date" name="dob" required />
      </div>
      <div class="befd-col">
        <label><?php echo esc_html($texts['gender_' . $lang]); ?></label>
        <select name="gender" required>
          <option value=""><?php echo esc_html($lang==='ja'?'選択':'Select'); ?></option>
          <option value="Male"><?php echo esc_html($lang==='ja'?'男性':'Male'); ?></option>
          <option value="Female"><?php echo esc_html($lang==='ja'?'女性':'Female'); ?></option>
        </select>
      </div>
    </div>
    <div class="befd-grid">
      <div class="befd-col">
        <label><?php echo esc_html($texts['phone_' . $lang]); ?></label>
        <input type="tel" name="phone" placeholder="<?php echo esc_attr($lang==='ja'?'電話番号を入力':'Enter Phone number here'); ?>" />
      </div>
      <div class="befd-col">
        <label><?php echo esc_html($texts['email_' . $lang]); ?></label>
        <input type="email" name="email" placeholder="<?php echo esc_attr($lang==='ja'?'メールアドレスを入力':'Enter email here'); ?>" required />
      </div>
    </div>

    <div class="befd-agree">
      <label><input type="checkbox" id="befd-agree" /> <?php echo esc_html($texts['agree_' . $lang]); ?></label>
    </div>

    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response" value="">
    <button type="submit" class="et_pb_button et_pb_button_0 befd-send" disabled><?php echo esc_html($texts['send_' . $lang]); ?></button>
  </form>
</div>
