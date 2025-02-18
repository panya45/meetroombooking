<?
namespace App\Http\Controllers\Auth;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Exception;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleCallback()
    {
        try {
            // ดึงข้อมูลผู้ใช้จาก Google
            $googleUser = Socialite::driver('google')->user();

            // ค้นหาผู้ใช้ในฐานข้อมูลตามอีเมล
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // หากไม่มีผู้ใช้ในระบบ จะสร้างผู้ใช้ใหม่
                $user = User::create([
                    'username' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(16)), // สร้างรหัสผ่านสุ่มกรณีไม่มี password
                ]);
            }

            // ตรวจสอบว่า Google ID ของผู้ใช้ตรงกับ Google User ID หรือไม่
            if ($user->google_id !== $googleUser->getId()) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            // เข้าสู่ระบบผู้ใช้
            Auth::login($user);

            return redirect()->route('home')->with('success', 'เข้าสู่ระบบด้วย Google สำเร็จ!');
        } catch (Exception $e) {
            return redirect('/')->with('error', 'เกิดข้อผิดพลาดในการล็อกอินผ่าน Google: ' . $e->getMessage());
        }
    }
}
