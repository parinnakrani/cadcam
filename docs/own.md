#### Subtask 2.1.4: Create GoldRateView

TASK: Generate GoldRateView for gold rate management UI

FILES:

- app\Views\GoldRates\create.php
- app\Views\GoldRates\edit.php
- app\Views\GoldRates\index.php
- app\Views\GoldRates\permissions.php

Follow design structure same as app\Views\Users files

Read .antigravity file and save whole content in your memory for this session
Also, read following content and save it for this session

Now, read the
Task-05-Payment-Management-COMPLETE.md
&
cadcam_invoice.sql
files and tell me if there is any mismatch for tasks and prompts, if there is first tell me what it is and then we need to correct it. Do not proceed with the tasks yet as this is just to check if there is any conflicts between tasks, prompts and database structure.
List out the difference.

We don't need Gold Adjustment Columns (Critical) in the payment table as gold adjustment only will be in invoice_line table
And do

- Proceed with 1. Missing Core Columns (Critical)
- Do not make change for 2. Missing Gold Adjustment Columns (Critical)
- Keep the current name conversion for 3. Naming Conflicts (Minor)
- Remove gold adjustment prompts and related things from the
  Task-05-Payment-Management-COMPLETE.md
  file
- Update the
  Task-05-Payment-Management-COMPLETE.md
  with new changes
