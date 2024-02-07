use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomPayloadToCmsTable extends Migration
{
    public function up()
    {
        Schema::table('cms', function (Blueprint $table) {
            $table->text('public_key')->nullable(),
            $table->text('helium_key')->nullable();

        });
    }

    public function down()
    {
        Schema::table('cms', function (Blueprint $table) {
            $table->dropColumn('public_key'),
            $table->dropColumn('helium_key');
        });
    }
}
