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

=> For Challan Create:

- Image for challan lines didn't get saved into database (image_path column), also make sure path is correctly saved which can be used to see same image in edit & view challan
- There will be no taxes for challan, remove taxes from code everywhere from challan. Keep the columns in database for now, just remove the taxes.
- We need to save current process prices into database, so we need to think about how can we manage to save prices for multiple processes as there can be more than one process per line item. There is process_prices columns in database in challan_lines table.
- Remove # column or (.line-number) column as it is taking space for other columns, we don't need that column
  => For Edit Challan:
- There are two line for challan_id=7 in database, you can check in @cadcam_invoice.sql
- Removing Process from any line is not updating the values for rate and amount. It should update the values for same rows if values get changed.
- Remove # column or (.line-number) column as it is taking space for other columns, we don't need that column
- Uploaded image for line should be visible and also it will be shown in modal if user clicks on image. There will be an option to remove image if there is any and clicking on it will show upload option. If user upload the image then it will be saved in database for that line.
- add "current_gold_price" column in challan lines table (it will be taken ("rate_per_gram") from session's company_id & selected gold_purity (metal_type) from companies table.
- If user select Gold Weight and Purity
- add "adjusted_gold_weight" & "gold_adjustment_amount" in the table as well
  it will be recalculate the amount using following formula
  adjusted_gold_weight = gold_weight - weight (that line's weight column value)
  new_final_amount = adjusted_gold_weight × rate_per_gram
  Now final amount will be new_final_amount for that line and gold_adjustment_amount = new_final_amount - amount (existing amount for that line in database)
  adjustment_amount = gold_difference × gold_rate_per_gram
- There will be no taxes for challan, remove taxes from code everywhere from challan. Keep the columns in database for now, just remove the taxes.

=====================
For edit challan, if we update any line item then in calulation gold weight is not getting taken into consideration.
First we need to fetch latest "rate_per_gram" from companies table for current company_id in session and metal_type (selected Purity in edit challan for that line)
Let's say for a line in database for a challan,

- Original amount (amount column in challan_lines table): ₹10,000
- Original weight (weight column in challan_lines table): 1
- User enter Gold Wt (g): 1.5
- Gold Difference will be 0.5 (Original weight - User's entered Gold Wt)
- User select Purity 22k
- Let's assume user has company_id = 1 in session, so we need to fetch latest value from companies (SELECT rate_per_gram FROM gold_rates WHERE company_id = 'SESSION_COMPANY_ID' AND metal_type = 'USER_SELECTED_PURITY' AND is_deleted = 0 ORDER BY created_at DESC LIMIT 1;). Let's assume it is 15,000

- final amount for that line will be (original amount + (gold*difference * current rate*per_gram))
  10,000 + (0.5 * 15000)= 17,500

- Also save Gold Difference (0.5) in adjusted_gold_weight, 7,500 in gold_adjustment_amount, 15,000 (rate_per_gram) in current_gold_price for that challan line
  Based on this final total of challan will also be changed

---

For Cash Invoice (http://localhost:81/cadcam-invoice/cash-invoices/create)

- If user starts typing for Customer Name it is showing suggestions below, it should also show suggestions if user starts typing in Mobile Number field as well. Also, Take design for it as Basic(#TypeaheadBasic) of Typeahead from
  forms-selects.html
  file. Furthermore, on selecting any customer from suggestions, it should fill both Customer Name and Mobile Number, currently it is only filling Customer Name textbox.
  Rest functionality should work as it is working currently, like selected customer id is getting saved in cash_customer_id field.
- Taxes are tax inclusive, currently it is showing as tax excusive. Need to change it to Inclusive and final it should now be more than total value of each invoice line.
