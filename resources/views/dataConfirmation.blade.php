<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Confirmation</title>
</head>

<body>
    <h1>Data Upload Summary</h1>
    <ul>
        <li>Total Rows Processed: {{ $totalRows }}</li>
        <li>Duplicate Rows: {{ $duplicates }}</li>
        <li>Valid Rows: {{ $validRows }}</li>
    </ul>

    <form action="{{ route('save.data.option') }}" method="POST">
        @csrf
        <p>What would you like to do?</p>
        <button name="action" value="save_valid">Save Valid Data (Recommended)</button>
        <button name="action" value="save_all">Save All Data (Includes Duplicates)</button>
        <button name="action" value="cancel">Cancel</button>
    </form>
</body>
</body>

</html>