<?php

namespace Lexx\ChatMessenger\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Eloquent
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'participants';

	/**
	 * The attributes that can be set with Mass Assignment.
	 *
	 * @var array
	 */
	protected $fillable = ['thread_id', 'user_id', 'company_id', 'last_read', 'starred'];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at', 'last_read'];

	/**
	 * attributes that should be cast
	 *
	 * @var array
	 */
	protected $casts = [
		'starred' => 'boolean',
	];

	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $attributes = [])
	{
		$this->table = Models::table('participants');

        parent::__construct($attributes);
    }

    /**
     * Thread relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     *
     * @codeCoverageIgnore
     */
    public function thread()
    {
        return $this->belongsTo(Models::classname(Thread::class), 'thread_id', 'id');
    }

	/**
	 * User relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 *
	 * @codeCoverageIgnore
	 */
	public function user()
	{
		return $this->belongsTo(Models::user(), 'user_id');
	}

	/**
	 * Company relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 *
	 */
	public function company()
	{
		return $this->belongsTo(Company::class, 'company_id');
	}
}
