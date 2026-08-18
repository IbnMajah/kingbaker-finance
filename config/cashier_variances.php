<?php

return [
    'high_value_threshold' => (float) env('CASHIER_VARIANCE_HIGH_VALUE_THRESHOLD', 500),
    'repeat_count' => (int) env('CASHIER_VARIANCE_REPEAT_COUNT', 3),
    'repeat_window_days' => (int) env('CASHIER_VARIANCE_REPEAT_WINDOW_DAYS', 30),
];
