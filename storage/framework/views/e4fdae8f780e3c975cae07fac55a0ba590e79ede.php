<?php if($paginator->hasPages()): ?>
<ul class="pagination no-margin pull-right">
  <?php if($paginator->onFirstPage()): ?>
  <li class="disabled"><span>&laquo;</span></li>
  <?php else: ?>
  <li><a href="<?php echo $paginator->previousPageUrl(); ?>">&laquo;</a></li>
  <?php endif; ?>
  <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(is_string($element)): ?>
    <li class="disabled"><span><?php echo $element; ?></span></li>
    <?php endif; ?>
    <?php if(is_array($element)): ?>
      <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($page == $paginator->currentPage()): ?>
        <li class="active"><span><?php echo $page; ?></span></li>
        <?php else: ?>
        <li><a href="<?php echo $url; ?>"><?php echo $page; ?></a></li>
        <?php endif; ?>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php if($paginator->hasMorePages()): ?>
  <li><a href="<?php echo $paginator->nextPageUrl(); ?>">&raquo;</a></li>
  <?php else: ?>
  <li class="disabled"><span>&raquo;</span></li>
  <?php endif; ?>
</ul>
<?php endif; ?>
<?php /**PATH /mnt/hgfs/www/zpp-Server4.0/resources/views/admin/pagination.blade.php ENDPATH**/ ?>