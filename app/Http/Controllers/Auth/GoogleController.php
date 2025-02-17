<?
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Exception;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => null, // ไม่ต้องใช้รหัสผ่านสำหรับ Social Login
                ]);
            }

            // อัปเดต Google ID เผื่อมีการล็อกอินครั้งแรกผ่านอีเมลก่อน
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            Auth::login($user);

            return redirect()->route('home')->with('success', 'เข้าสู่ระบบด้วย Google สำเร็จ!');
        } catch (Exception $e) {
            return redirect('/')->with('error', 'เกิดข้อผิดพลาดในการล็อกอินผ่าน Google');
        }
    }
}
