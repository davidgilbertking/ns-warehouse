<div class="mb-3">
    <label class="form-label">Медиа (фото/видео)</label>
    <div id="realMediaWrapper">
        <input type="text" name="real_media[]" class="form-control mb-2" placeholder="Ссылка на медиа">
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addMediaField('realMediaWrapper', 'real_media[]')">+ Добавить</button>
</div>

<div class="mb-3">
    <label for="construction_description" class="form-label">Описание материалов и конструкции</label>
    <textarea id="construction_description" name="construction_description" class="form-control" rows="2"></textarea>
</div>

<div class="mb-3">
    <label for="contractor" class="form-label">Подрядчик</label>
    <input type="text" id="contractor" name="contractor" class="form-control">
</div>

<div class="mb-3">
    <label for="production_cost" class="form-label">Стоимость производства/закупки</label>
    <input type="text" id="production_cost" name="production_cost" class="form-control">
</div>

<div class="mb-3">
    <label for="change_history" class="form-label">История изменений</label>
    <textarea id="change_history" name="change_history" class="form-control" rows="2"></textarea>
</div>

<div class="mb-3">
    <label for="consumables" class="form-label">Расходники</label>
    <textarea id="consumables" name="consumables" class="form-control" rows="2"></textarea>
</div>

<div class="mb-3">
    <label for="implementation_comments" class="form-label">Доп. комментарии</label>
    <textarea id="implementation_comments" name="implementation_comments" class="form-control" rows="2"></textarea>
</div>

<div class="mb-3">
    <label for="mounting" class="form-label">Монтаж/демонтаж</label>
    <textarea id="mounting" name="mounting" class="form-control" rows="2"></textarea>
</div>

<div class="mb-3">
    <label for="storage_features" class="form-label">Особенности хранения и транспортировки</label>
    <textarea id="storage_features" name="storage_features" class="form-control" rows="2"></textarea>
</div>

<div class="mb-3">
    <label for="storage_place" class="form-label">Место хранения</label>
    <input type="text" id="storage_place" name="storage_place" class="form-control">
</div>

<div class="mb-3">
    <label for="design_links" class="form-label">Ссылка на макеты/исходники</label>
    <input type="text" id="design_links" name="design_links" class="form-control">
</div>
