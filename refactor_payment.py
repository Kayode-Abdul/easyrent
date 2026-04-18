import re

with open('/Applications/XAMPP/xamppfiles/htdocs/easyrent/app/Http/Controllers/PaymentController.php', 'r') as f:
    content = f.read()

start_pattern = r"if \(\$paymentDetails\['status'\] && \$paymentDetails\['data'\]\['status'\] === 'success'\) \{"
end_pattern = r"            Log::warning\('Payment verification failed', \['payment_details' => \$paymentDetails \?\? null\]\);"

start_match = re.search(start_pattern, content)
end_match = re.search(end_pattern, content)

if not start_match or not end_match:
    print("Could not find patterns")
    exit(1)

block = content[start_match.end():end_match.start()]

extracted_method = """
    protected function processVerifiedPayment(array $paymentData, string $gateway, string $transactionReference = null)
    {
        $reference = $gateway === 'paystack' ? ($paymentData['reference'] ?? null) : ($paymentData['tx_ref'] ?? null);
        if (!$transactionReference) {
            $transactionReference = $reference;
        }
        
        $metadata = $gateway === 'paystack' ? ($paymentData['metadata'] ?? []) : ($paymentData['meta'] ?? []);
        $amount = $gateway === 'paystack' ? ($paymentData['amount'] / 100) : $paymentData['amount'];
        $channel = $gateway === 'paystack' ? ($paymentData['channel'] ?? 'card') : ($paymentData['payment_type'] ?? 'card');
        
"""

# Replace variables in the block
block = block.replace("$paymentDetails['data']['amount'] / 100", "$amount")
block = block.replace("$paymentDetails['data']['amount']", "($amount * 100)")
block = block.replace("$paymentDetails['data']['channel']", "$channel")
block = block.replace("$paymentDetails['data']['gateway_response']", "($paymentData['gateway_response'] ?? $paymentData['processor_response'] ?? null)")
block = block.replace("$paymentDetails['data']['paid_at']", "($paymentData['paid_at'] ?? $paymentData['created_at'] ?? null)")
block = block.replace("$paymentDetails['data']['reference']", "$reference")
block = block.replace("$paymentDetails['data']['metadata']", "$metadata")
block = block.replace("$paymentDetails['data']", "$paymentData")

extracted_method += block
extracted_method += "    }\n"

new_block = """
                return $this->processVerifiedPayment($paymentDetails['data'], 'paystack', $transactionReference);
            }
"""

new_content = content[:start_match.start()] + start_match.group(0) + new_block + content[end_match.start():]

insert_pos = new_content.find("private function generateReceipt")
if insert_pos == -1:
    print("Could not find generateReceipt placeholder")
    exit(1)

new_content = new_content[:insert_pos] + extracted_method + "\n    " + new_content[insert_pos:]

with open('/Applications/XAMPP/xamppfiles/htdocs/easyrent/app/Http/Controllers/PaymentController.php', 'w') as f:
    f.write(new_content)

print("Refactoring complete")
