<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="<?php echo URL::asset('web/docs/vendor.css?v=000'); ?>">
  <style>
  /* override */
  .is-collapsible {
    max-height: none;
  }

  .is-collapsed {
    max-height: 0;
  }

  /* custom */
  .js-md,
  .js-toc {
    position: fixed;
    top: 20px;
  }

  .js-toc {
    right: 58.333333%;
    left: 16.666667%;

    overflow-y: auto;

    height: 800px;
  }

  .js-toc ol {
    list-style: none;
  }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <div class="col-2">
        <div class="js-md">
        <ol>
          <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><a href="javascript:;"><?php echo e($file); ?></a></li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
        </div>
      </div>
      <div class="col-3">
        <div class="js-toc"></div>
      </div>
      <div class="col-7">
        <div class="js-content"></div>
      </div>
    </div>
  </div>
  <script src="<?php echo URL::asset('web/docs/vendor.js?v=000'); ?>"></script>
  <script>
  $(function() {
    $('.js-md')
      .on('click', 'a', function(ev) {
        ev.preventDefault();

        $.ajax({
          method: 'get',
          url: 'docs/md',
          data: {
            file: $(this).text(),
            timestamp: Date.now(),
          },
        }).then(function(res) {
          if (res.code == 200) {
            marked.reset();

            $('.js-content').html(marked(res.data.md));

            tocbot.destroy();

            tocbot.init({
              tocSelector: '.js-toc',
              contentSelector: '.js-content',
              headingSelector: 'h1, h2, h3, h4, h5, h6',
            });
          }
        });
      })
      .find('a')
      .first()
      .click()
    ;
  });
  </script>
</body>

</html>
<?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/web/docs/index.blade.php ENDPATH**/ ?>