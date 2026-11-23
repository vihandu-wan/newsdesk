<form method="post" enctype="multipart/form-data" class="form panel" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="full">
            <label for="title">Title *</label>
            <input id="title" name="title" required minlength="5" maxlength="255" data-counter value="<?= e($values['title']) ?>">
            <?php if (isset($errors['title'])): ?><small class="field-error"><?= e($errors['title']) ?></small><?php endif; ?>
        </div>
        <div>
            <label for="category_id">Category *</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select category</option>
                <?php foreach (get_categories($pdo) as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= (int) $values['category_id'] === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['category_id'])): ?><small class="field-error"><?= e($errors['category_id']) ?></small><?php endif; ?>
        </div>
        <div>
            <label for="status">Status *</label>
            <select id="status" name="status">
                <option value="draft" <?= $values['status'] === 'draft' ? 'selected' : '' ?>>Draft (hidden)</option>
                <option value="published" <?= $values['status'] === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </div>
        <div class="full">
            <label for="content">Content *</label>
            <textarea id="content" name="content" rows="14" required minlength="20"><?= e($values['content']) ?></textarea>
            <?php if (isset($errors['content'])): ?><small class="field-error"><?= e($errors['content']) ?></small><?php endif; ?>
        </div>
        <div>
            <label for="image">Cover image (JPG/PNG/WEBP/GIF, max 2 MB)</label>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,.gif" data-preview="img-preview">
            <img id="img-preview" class="preview-img" src="<?= e(imageSrc($values['image_url'] ?? '')) ?>" alt=""
                 <?= empty($values['image_url']) ? 'hidden' : '' ?>>
            <?php if (isset($errors['image'])): ?><small class="field-error"><?= e($errors['image']) ?></small><?php endif; ?>
            <?php if (!empty($values['image_url'])): ?>
                <label class="check"><input type="checkbox" name="remove_image" value="1"> Remove current image</label>
            <?php endif; ?>
        </div>
        <div>
            <label>Highlight</label>
            <label class="check"><input type="checkbox" name="is_featured" value="1" <?= $values['is_featured'] ? 'checked' : '' ?>> Featured on home page</label>
            <label class="check"><input type="checkbox" name="is_breaking" value="1" <?= $values['is_breaking'] ? 'checked' : '' ?>> Show in breaking-news ticker</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-block"><?= e($submitLabel) ?></button>
</form>
