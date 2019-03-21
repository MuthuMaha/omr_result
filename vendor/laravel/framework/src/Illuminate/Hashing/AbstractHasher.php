<?php

namespace Illuminate\Hashing;
use App\Employee;
abstract class AbstractHasher
{
    /**
     * Get information about the given hashed value.
     *
     * @param  string $hashedValue
     * @return array
     */
    public function info($hashedValue)
    {
        return password_get_info($hashedValue);
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function check($value, $hashedValue, array $options = [])
    {
        // if (strlen($hashedValue) === 0) {
        //     return false;
        // }

        // return password_verify($value, $hashedValue);
        $user = Employee::wherePassword(md5($value))->first();
      return $user ? true : false;
        if (strlen($hashedValue) === 0) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }
}
