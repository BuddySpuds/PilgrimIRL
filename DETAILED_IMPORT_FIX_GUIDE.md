# Detailed Guide: Fixing Cork and Down Import Issues

## The Problem Explained

**Why Cork and Down Failed to Import:**
- Cork has 120 monastic sites (largest file)
- Down has 54 monastic sites (second largest)
- PHP has default memory limits that get exceeded when processing large JSON files
- Your import shows: "❌ Failed to parse JSON for Cork" and "❌ Failed to parse JSON for Down"

## Solution 1: Increase PHP Memory Limits (Recommended)

### Step-by-Step Instructions:

#### 1. Find Your wp-config.php File
- In VS Code, look for `wp-config.php` in your project root
- It should be at: `/Users/robertporter/Local Sites/pilgrimirl/app/public/wp-config.php`

#### 2. Edit wp-config.php
Open the file and add these lines **BEFORE** the line that says `/* That's all, stop editing! */`:

```php
// Increase memory limits for data import
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);
ini_set('max_input_vars', 3000);
```

#### 3. What These Lines Do:
- `memory_limit`: Increases from default 128M to 512M (4x more memory)
- `max_execution_time`: Increases from 30 seconds to 300 seconds (5 minutes)
- `max_input_vars`: Increases from 1000 to 3000 variables (for large forms)

#### 4. Save and Test
- Save the file
- Go back to your import page
- Try importing Cork and Down again

## Solution 2: Split Large Files (Alternative Method)

If Solution 1 doesn't work, split the files manually:

### For Cork (120 sites → 4 batches of 30):

#### 1. Open Cork File
- Open `MonasticSites_JSON/Cork-enriched.json`
- Copy the entire content

#### 2. Create 4 New Files:
**Cork-batch1.json** (sites 1-30):
```json
[
  {first 30 sites from Cork file}
]
```

**Cork-batch2.json** (sites 31-60):
```json
[
  {sites 31-60 from Cork file}
]
```

**Cork-batch3.json** (sites 61-90):
```json
[
  {sites 61-90 from Cork file}
]
```

**Cork-batch4.json** (sites 91-120):
```json
[
  {remaining sites from Cork file}
]
```

#### 3. Import Each Batch Separately
- Import Cork-batch1.json first
- Wait for it to complete
- Then import Cork-batch2.json
- Continue until all 4 batches are imported

### For Down (54 sites → 2 batches of 27):

**Down-batch1.json** (sites 1-27):
```json
[
  {first 27 sites from Down file}
]
```

**Down-batch2.json** (sites 28-54):
```json
[
  {remaining 27 sites from Down file}
]
```

## Which Method to Choose?

### Use Solution 1 (PHP Memory) If:
- You're comfortable editing wp-config.php
- You want a permanent fix
- You plan to import more large files later

### Use Solution 2 (Split Files) If:
- You don't want to modify PHP settings
- You prefer a one-time manual approach
- Solution 1 doesn't work for some reason

## How to Check if It Worked

After applying either solution:

1. Go to your import page
2. Try importing Cork again
3. Look for: "✅ Imported X sites from Cork" instead of "❌ Failed to parse JSON for Cork"
4. Do the same for Down

## Expected Results After Fix

You should see:
- ✅ Imported 120 sites from Cork
- ✅ Imported 54 sites from Down
- Total sites imported: 174 additional sites
- Cork county page will show 120 sites instead of 0
- Down county page will show 54 sites instead of 0

## Troubleshooting

**If Solution 1 doesn't work:**
- Try increasing memory_limit to '1024M' (1GB)
- Check if Local by Flywheel has its own PHP settings

**If Solution 2 is too tedious:**
- I can help you create a script to automatically split the files
- Or we can modify the import function to process in smaller chunks

**If both fail:**
- Check the PHP error logs in Local by Flywheel
- The issue might be something else entirely

---

**Recommendation: Start with Solution 1 - it's easier and fixes the root cause.**
