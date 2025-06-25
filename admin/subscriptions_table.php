<div class="table-responsive">
  <table class="table table-bordered align-middle text-center bg-white shadow-sm" style="border-radius:16px;overflow:hidden;">
    <thead class="table-info"><tr>
      <th>الاسم</th>
      <th>الصفة</th>
      <th>تاريخ البداية</th>
      <th>تاريخ النهاية</th>
      <th>طريقة الدفع</th>
      <th>المرجع</th>
      <th>الحالة</th>
      <th>إجراءات</th>
    </tr></thead>
    <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['user'])?></td>
          <td>
            <?php if($r['role']=='doctor'): ?>
              <span class="badge bg-success">طبيب</span>
            <?php else: ?>
              <span class="badge bg-primary">مريض</span>
            <?php endif; ?>
          </td>
          <td><?=htmlspecialchars($r['start_date'])?></td>
          <td><?=htmlspecialchars($r['end_date'])?></td>
          <td><?=htmlspecialchars($r['payment_method'])?></td>
          <td><?=htmlspecialchars($r['payment_ref'])?></td>
          <td>
            <?php if($r['status'] == 'pending'): ?>
              <span class="badge bg-warning">بانتظار الموافقة</span>
            <?php elseif($r['status']=='active'): ?>
              <span class="badge bg-success">مفعّل</span>
            <?php elseif($r['status']=='rejected'): ?>
              <span class="badge bg-danger">مرفوض</span>
            <?php elseif($r['status']=='paused'): ?>
              <span class="badge bg-secondary">موقوف</span>
            <?php else: ?>
              <span class="badge bg-info"><?=$r['status']?></span>
            <?php endif; ?>
          </td>
          <td>
            <a href="subscription_invoice.php?id=<?=$r['id']?>" target="_blank" class="btn btn-info btn-sm"><i class="bi bi-printer"></i> فاتورة</a>
            <?php if($r['status']=='pending'): ?>
              <a href="?approve=<?=$r['id']?>" class="btn btn-success btn-sm">تفعيل</a>
              <a href="?reject=<?=$r['id']?>" class="btn btn-warning btn-sm">رفض</a>
            <?php elseif($r['status']=='active'): ?>
              <a href="?pause=<?=$r['id']?>" class="btn btn-warning btn-sm">توقيف</a>
              <a href="?extend=<?=$r['id']?>" class="btn btn-success btn-sm">تمديد شهر</a>
            <?php endif; ?>
            <a href="?del=<?=$r['id']?>" onclick="return confirm('تأكيد حذف الاشتراك؟')" class="btn btn-danger btn-sm">حذف</a>
          </td>
        </tr>
      <?php endforeach;?>
      <?php if(empty($rows)): ?>
          <tr><td colspan="8" class="text-muted">لا توجد اشتراكات بعد.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>