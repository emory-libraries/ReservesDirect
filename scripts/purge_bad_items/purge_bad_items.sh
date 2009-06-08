#!/bin/sh

echo "alter item group ... "
/usr/local/bin/php alter_item_group.php > alter_item_group.out
echo "done"
 
echo "remove invalid items ..."
/usr/local/bin/php remove_invalid_items.php > remove_invalid_items.out
echo "done"

echo "update Control Keys From Barcode"
/usr/local/bin/php updateControlKeyFromBarcode.php > updateControlKeyFromBarcode.out
echo "done"

echo "remove duplicate items ..."
/usr/local/bin/php remove_duplicate_items.php > remove_duplicate_items.out
echo "done"

echo "remove duplicate reserves ..."
/usr/local/bin/php remove_duplicate_reserves.php > remove_duplicate_reserves.out
echo "done"

echo "remove duplicate notes ..."
/usr/local/bin/php remove_duplicate_notes.php > remove_duplicate_notes.out
echo "done"

echo "creating new indexes ..."
/usr/local/bin/php new_reserves_ndx.php
echo "done"
